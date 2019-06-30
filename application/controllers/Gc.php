<?php
class GcController extends \Explorer\ControllerAbstract {

    public function textAction() {
        $query = $this->getRequest()->getQuery('query');
        if (!$query) {
            return $this->outputError(Constants::ERR_GC_QUERY_INVALID, '请求无效');
        }
        $gcModel = new GcModel();
        $result = $gcModel->fetch($query);
        if (!$result) {
            return $this->outputError(Constants::ERR_GC_NOT_FOUND, '未找到');
        }
        $this->outputSuccess(compact('result'));
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
        $mp3_name = $uuid.'.mp3';
        $pcm_name = $uuid.'.pcm';
        if ($file['error'] == 0 && !empty($file['name'])) {
            move_uploaded_file($file['tmp_name'], $upload_path.'/'.$mp3_name);
        } else {
            return $this->outputError(Constants::ERR_GC_AUDIO_NOT_EXIST, '请重试');
        }

        $transcode = "ffmpeg -y -i $upload_path/$mp3_name -acodec pcm_s16le -f s16le -ac 1 -ar 16000 $upload_path/$pcm_name";
        exec($transcode);

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

        $gcModel = new GcModel();
        $result = $gcModel->fetch($query);
        if (!$result) {
            return $this->outputError(Constants::ERR_GC_NOT_FOUND, '未找到');
        }
        $this->outputSuccess(compact('result'));
    }

}
