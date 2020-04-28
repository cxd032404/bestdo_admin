<?php
/**
 * 文件上传
 * @author Justin.Chen <cxd032404@hotmail.com>
 *
 * $Id: Upload.php 15195 2014-07-23 07:18:26Z 334746 $
 */


class Base_Upload
{
    /**
     * 只允许上传图片文件
     * @var boolean
     */
	protected $onlyAllowImage = true;

	/**
	 * 允许的文件后缀
	 * @var array
	 */
	//protected $allowFileExtArr = array('gif', 'jpg', 'jpeg', 'png', 'bmp', 'rar', 'zip', 'txt','log');

	/**
	 * 允许的图片后缀
	 * @var array
	 */
	//protected $allowImageExtArr = array('gif', 'jpg', 'jpeg', 'png', 'bmp');

	protected $allowFileExtArr = [
	    "txt" => ["txt"],
        "img" => ['gif', 'jpg', 'jpeg', 'png', 'bmp'],
        "video" => []
    ];
	/**
	 * 最大文件
	 * @var integer
	 */
	protected $maxFileSize = 1073741824;

	/**
	 * 待上传的文件
	 * @var array
	 */
	protected $fileArr = array();

	/**
	 * 已上传的文件
	 * @var array
	 */
	protected $uploadedFileArr = array();

	/**
	 * 上传结果
	 * @var array
	 */
	//protected $resultArr = array();
	public $resultArr = array();

	protected $savePath;

	/**
	 * 文件上传目录
	 * @var string
	 */
	protected $fileDir;
	protected $fileUrl;

	public function __construct($name, array $descriptionArr = array())
	{
		$this->fileDir = Base_Common::$config['file_dir'];
		$this->fileUrl = Base_Common::$config['file_url'];
		$noFileArr = array();
		if (isset($_FILES[$name]) && is_array($_FILES[$name]))
		{		 
			foreach ($_FILES[$name] as $key => $val) 
			{
				foreach ($val as $k => $v)
				{
					if ($v == UPLOAD_ERR_NO_FILE) 
					{
						$noFileArr[] = $k;
					}
					$this->fileArr[$k][$key] = $v;
					$this->fileArr[$k]['description'] = empty($descriptionArr[$k]) ? '' : $descriptionArr[$k];
				}
			}
		}
		else
		{
			$this->fileArr = array();
		}


		foreach ($noFileArr as $k) {
			unset($this->fileArr[$k]);
		}
	}

	public function setFileDir($dir)
	{
	    $this->fileDir = $dir;
	    return $this;
	}

	public function upload($path,$ossConfig = [])
	{
		$this->savePath = $path;
		if (!is_dir($this->fileDir . $this->savePath) && !mkdir($this->fileDir . $this->savePath)) {
			throw new Base_Exception(sprintf('目录 %s 不存在', $this->fileDir . $this->savePath), 403);
		}
		$t = explode("_",$path);
		$fileType = $t[count($t)-1];

		@chmod($this->fileDir . $this->savePath);

		if (!is_writeable($this->fileDir . $this->savePath)) {
			throw new Base_Exception(sprintf('目录 %s 不可写', $this->fileDir . $this->savePath), 403);
		}
		foreach ($this->fileArr as $k => $file)
		{
			if (!self::isUploadedFile($file['tmp_name'])) {
				$this->resultArr[$k]['errno'] = 1;
				$this->resultArr[$k]['description'] = '文件上传失败';
				continue;
			}
			$suffix = Base_Common::fileSuffix($file['name']);
			$fileTypeCheck = in_array($suffix, $this->allowFileExtArr[$fileType]??[]);

			if (!$fileTypeCheck ) {
				$this->resultArr[$k]['errno'] = 2;
				$this->resultArr[$k]['description'] = '不允许上传非允许类型文件';
				continue;
			}

			if ($file['size'] > $this->maxFileSize) {
				$this->resultArr[$k]['errno'] = 4;
				$this->resultArr[$k]['description'] = '文件大小超过限制';
				continue;
			}

			$filename =  $file['name'];
			$target = $this->fileDir . $this->savePath . '/' . $filename;
			$target_root = $this->fileUrl."/".$this->savePath.'/' . $filename;
			
			if (move_uploaded_file($file['tmp_name'], $target) || @copy($file['tmp_name'], $target)) {
				$this->resultArr[$k]['errno'] = 0;
				$this->resultArr[$k]['description'] = '文件上传成功';
				$this->resultArr[$k]['path'] = $target;
				$this->resultArr[$k]['size'] = $file['size'];
                $this->resultArr[$k]['type'] = $file['type'];
                $this->resultArr[$k]['path_root'] = $target_root;
			} else {
				$this->resultArr[$k]['errno'] = 5;
				$this->resultArr[$k]['description'] = '文件上传失败';
			}

		}
        if(count($ossConfig) >= 1)
        {
            $oss = Third_aliyun_oss_OssClientFile::upload2Oss($this->resultArr,$ossConfig);
            foreach($oss as $k => $ossFile)
            {
                $this->resultArr[$k]['oss'] = $ossFile['info']['url']??"";
            }
        }
		return $this;
	}

	public static function isUploadedFile($file)
	{
		return is_uploaded_file(str_replace('\\\\', '\\', $file));
	}

	public function setOnlyAllowImage()
	{
		$this->onlyAllowImage = true;
		return $this;
	}

	public function getResult()
	{
		return $this->resultArr;
	}

	public function getUploadedFile()
	{
		return $this->uploadedFileArr;
	}

}
