<?php
/**
 * 上传账单文件到ali_oss
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/27
 * Time: 15:32
 */

namespace wenruns\payment\Traits;


Trait AliOssTrait
{
    /**
     * 上传ali_oss失败次数
     * @var int
     */
    protected $upload_ali_oss_fail_times = 0;

    /**
     * 上传ali_oss失败，尝试上传次数
     * @var int
     */
    protected $upload_ali_oss_try_times = 0;


    /**
     * 账单文件地址
     * @var string
     */
    protected $bill_file_path = '';

    /**
     * 是否删除本地缓存文件
     * @var bool
     */
    protected $delete_local_file = false;

    /**
     * 是否上传ali_oss
     * @var bool
     */
    protected $upload_bill_file_to_ali_oss = false;


    /**
     * 上传账单文件到ali_oss
     * @param $content
     * @param $postPath
     * @param $file_name
     * @return bool|null
     */
    protected function saveOriginDataFile($content, $postPath, $file_name)
    {
        $file_path = storage_path($postPath);
        if (!is_dir($file_path)) {
            mkdir($file_path, 0777, true);
            chmod($file_path, 0777);
        }
        // 生成账单文件
        file_put_contents($file_path . '/' . $file_name, $content);
        if (is_file($file_path . '/' . $file_name)) {
            $this->bill_file_path = $file_path . '/' . $file_name;
            $file_id = $this->postFile($file_name, $postPath);
            // 删除本地文件
            if ($this->delete_local_file) {
                unlink($this->bill_file_path);
            }
            return $file_id;
        }
        return false;
    }

    /**
     * 提交ali_oss
     * @param $file_name
     * @param $postPath
     * @return bool|int
     */
    protected function postFile($file_name, $postPath)
    {
        if (!is_file($this->bill_file_path)) {
            return false;
        }
        // todo::上传ali_oss
        $res = $this->upload($file_name, $postPath);
        // 上传ali_oss失败重试
        if (!$res && $this->upload_ali_oss_fail_times < $this->upload_ali_oss_try_times) {
            $this->upload_ali_oss_fail_times++;
            return $this->postFile($file_name);
        }
        return $res;
    }

    /**
     * 上传ali_oss
     * @param $fileName
     * @param $postPath
     */
    protected function upload($fileName, $postPath)
    {

    }
}