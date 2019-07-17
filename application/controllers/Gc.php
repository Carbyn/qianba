<?php
class GcController extends \Explorer\ControllerAbstract {

    private $classifications = [
        0 => '',
        1 => '可回收物',
        2 => '有害垃圾',
        3 => '湿垃圾',
        4 => '干垃圾',
    ];

    public function textAction() {
        $query = $this->getRequest()->getQuery('query');
        if (!$query) {
            return $this->outputError(Constants::ERR_GC_QUERY_INVALID, '请求无效');
        }
        $query = $this->identifyEntity($query);
        $gcModel = new GcModel();
        $result = $gcModel->fetchDB($query);
        if ($result) {
            $result->classification = $this->makeReadable($result->classification);
            $result = $this->classifications[$result->classification];
            return $this->outputSuccess(compact('result', 'query'));
        }
        $result = $gcModel->fetch($query);
        if (!$result) {
            $this->save($query, '');
            return $this->outputError(Constants::ERR_GC_NOT_FOUND, '未找到', compact('query'));
        }

        $this->save($query, $result);

        $this->outputSuccess(compact('result', 'query'));
    }

    public function voiceAction() {
        $upload_path = APPLICATION_PATH.'/uploads';
        if (!@file_exists($upload_path)) {
            mkdir($upload_path);
        }
        $name = 'audio';
        $files = $this->getRequest()->getFiles();
        if (empty($files[$name])) {
            return $this->outputError(Constants::ERR_GC_AUDIO_NOT_EXIST, '请重试');
        }
        $file = $files[$name];
        $uuid = uniqid(true);
        $parts = pathinfo($file['name']);
        $extension = $parts['extension'];
        $original_name = $uuid.'.'.$extension;
        $pcm_name = $uuid.'.pcm';
        if ($file['error'] == 0 && !empty($file['name'])) {
            move_uploaded_file($file['tmp_name'], $upload_path.'/'.$original_name);
        } else {
            return $this->outputError(Constants::ERR_GC_AUDIO_NOT_EXIST, '请重试');
        }

        if ($extension != 'pcm') {
            $transcode = "ffmpeg -y -i $upload_path/$original_name -acodec pcm_s16le -f s16le -ac 1 -ar 16000 $upload_path/$pcm_name";
            exec($transcode);
        }

        $aipSpeech = \Explorer\Aip::getInstance();
        $result = $aipSpeech->asr(file_get_contents($upload_path.'/'.$pcm_name), 'pcm', 16000, ['dev_pid' => 80001]);
        if (!$result) {
            return $this->outputError(Constants::ERR_GC_AIP_FAILED, '服务出问题了，请稍后重试');
        }
        // pro超限,转用免费版
        if ($result['err_no'] == 3305) {
            $aipSpeech->pro = false;
            $result = $aipSpeech->asr(file_get_contents($upload_path.'/'.$pcm_name), 'pcm', 16000, ['dev_pid' => 1536]);
        }
        if (!$result) {
            return $this->outputError(Constants::ERR_GC_AIP_FAILED, '服务出问题了，请稍后重试');
        }
        if ($result['err_no'] != 0) {
            return $this->outputError(Constants::ERR_GC_AIP_FAILED, '服务出问题了，请稍后重试');
        }

        $query = $result['result'][0];
        if ($aipSpeech->pro) {
            $query = mb_substr($query, 0, -1);
        }
        if (!$query) {
            return $this->outputError(Constants::ERR_GC_AIP_FAILED, '服务出问题了，请稍后重试');
        }

        $query = $this->identifyEntity($query);
        $gcModel = new GcModel();
        $result = $gcModel->fetchDB($query);
        if ($result) {
            $result->classification = $this->makeReadable($result->classification);
            $result = $this->classifications[$result->classification];
            return $this->outputSuccess(compact('result', 'query'));
        }

        $result = $gcModel->fetch($query);
        if (!$result) {
            $this->save($query, '');
            return $this->outputError(Constants::ERR_GC_NOT_FOUND, '未找到', compact('query'));
        }

        $this->save($query, $result);

        $this->outputSuccess(compact('result', 'query'));
    }

    public function quizAction() {
        $gcModel = new GcModel();

        $garbages = [];
        $i = 0;
        $used = 0;
        foreach ($this->classifications as $k => $v) {
            if ($k == 0 ) {
                continue;
            }
            if ($i + 1 == count($this->classifications) - 1) {
                $batchSize = 10 - $used;
            } else {
                $batchSize = rand(1, 3);
            }
            $garbages = array_merge($garbages, $gcModel->fetchBatch($k, $batchSize));
            $i++;
            $used += $batchSize;
        }
        shuffle($garbages);

        foreach($garbages as &$g) {
            $g->classification = $this->classifications[$g->classification];
        }

        return $this->outputSuccess(compact('garbages'));
    }

    public function listAction() {
        $gcModel = new GcModel();

        $data = $gcModel->fetchAllFound();

        $garbages = [];
        foreach($data as $row) {
            if ($row->classification >= 10) {
                continue;
            }
            $garbages[$this->classifications[$row->classification]][] = $row->garbage;
        }

        return $this->outputSuccess(compact('garbages'));
    }

    public function feedAction() {
        $page = $this->getRequest()->getQuery('page', 1);
        $pagesize = 10;
        $articleModel = new ArticleModel();
        $article = $articleModel->fetchAll($page, $pagesize);
        $is_end = count($article) < $pagesize;
        $num = 9;
        $this->outputSuccess(compact('article', 'is_end', 'num'));
    }

    public function detailAction() {
        $id = $this->getRequest()->getQuery('id');
        if (!$id) {
            return $this->outputError(Constants::ERR_ARTICLE_NOT_FOUND, '文章不存在');
        }
        $articleModel = new ArticleModel();
        $article = $articleModel->fetch($id);
        if (!$article) {
            return $this->outputError(Constants::ERR_ARTICLE_NOT_FOUND, '文章不存在');
        }
        $this->outputSuccess(compact('article'));
    }

    public function exportAction() {
        $gcModel = new GcModel();
        if ($this->getRequest()->getQuery('all')) {
            $data = $gcModel->fetchAll();
        } else {
            $data = $gcModel->fetchAllNotFound();
        }
        $exportFile = APPLICATION_PATH.'/uploads/export.txt';
        exec('rm -f '.$exportFile);
        for ($i = 0; $i < count($data); $i++) {
            $row = $data[$i];
            $line = $row->garbage.','.$row->classification.','.$row->count."\n";
            file_put_contents($exportFile, $line, FILE_APPEND);
        }
        $sender = new \diversen\sendfile();
        try {
            $ret = $sender->send($exportFile);
        } catch (\Exception $e) {
            return $this->outputError(Constants::ERR_GC_EXPORT_FAILED, '导出失败');
        }
    }

    public function importAction() {
        $upload_path = APPLICATION_PATH.'/uploads';
        $name = 'file';
        $files = $this->getRequest()->getFiles();
        if (empty($files[$name])) {
            return $this->outputError(Constants::ERR_GC_IMPORT_FILE_NOT_EXIST, '请上传文件');
        }
        $file = $files[$name];
        if ($file['error'] == 0 && !empty($file['name'])) {
            move_uploaded_file($file['tmp_name'], $upload_path.'/'.$name);
        } else {
            return $this->outputError(Constants::ERR_GC_IMPORT_FILE_NOT_EXIST, '请上传文件');
        }
        $lines = file_get_contents($upload_path.'/'.$name);
        $lines = explode("\n", $lines);
        $gcModel = new GcModel();
        foreach($lines as $line) {
            if (trim($line)) {
                list($garbage, $classification, $count) = explode(',', trim($line));
                if (!$garbage) {
                    continue;
                }
                $classification = intval($classification);
                if ($gcModel->exists($garbage)) {
                    $gcModel->update($garbage, $classification);
                } else {
                    $data = compact('garbage', 'classification');
                    $gcModel->create($data);
                }
            }
        }
        $this->outputSuccess();
    }

    private function save($garbage, $classification) {
        $gcModel = new GcModel();
        if ($gcModel->exists($garbage)) {
            if ($classification) {
                $gcModel->update($garbage, array_search($classification, $this->classifications));
            }
            return;
        }
        $data = [
            'garbage' => $garbage,
            'classification' => array_search($classification, $this->classifications),
        ];
        $gcModel->create($data);
    }

    private function identifyEntity($query) {
        $pos[] = mb_strpos($query, '是');
        $pos[] = mb_strpos($query, '属于');
        $pos[] = mb_strpos($query, '放到');
        $realPos = 0;
        foreach($pos as $p) {
            if ($p !== false) {
                if ($realPos == 0) {
                    $realPos = $p;
                } else {
                    $realPos = min($p, $realPos);
                }
            }
        }
        if ($realPos > 0) {
            $query = mb_substr($query, 0, $realPos);
        }
        return $query;
    }

    private function makeReadable($classification) {
        return $classification >= 10 ? $classification / 10 : $classification;
    }

}
