<?php
class UploaderController extends \Explorer\ControllerAbstract {

    public function imageAction() {
        if (!$this->userId) {
            return $this->outputError(Constants::ERR_SYS_NOT_LOGGED, '请先登录');
        }
	    $upload_path = APPLICATION_PATH.'/uploads';
	    if (!@file_exists($upload_path)) {
	        mkdir($upload_path);
	    }
        $name = 'image';
	    $files = $this->getRequest()->getFiles();
	    if (empty($files[$name])) {
            return $this->outputError(Constants::ERR_UPLOADER_NO_IMAGE, '请上传图片');
	    }
        $file = $files[$name];
        $tmp = explode('.', $file['name']);
        $ext = '.'.$tmp[count($tmp) - 1];
        $img_name = uniqid(true).$ext;
        if ($file['error'] == 0 && !empty($file['name'])) {
            move_uploaded_file($file['tmp_name'], $upload_path.'/'.$img_name);
        } else {
            return $this->outputError(Constants::ERR_UPLOADER_FAILED, '请重试');
        }
        $image = 'https://qianba.1024.pm/uploads/'.$img_name;
        $this->outputSuccess(compact('image'));
    }

}
