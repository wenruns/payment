<?php
/**
 * xml字符串处理
 * Created by PhpStorm.
 * User: 文
 * Date: 2020/3/26
 * Time: 16:56
 */

namespace wenruns\payment\Traits;


trait XmlTrait
{
    /**
     * 格式化参数为xml格式
     * @param $data
     * @return string
     */
    protected function toXml($data)
    {
        $str = '<xml>';
        $str .= $this->makeXmlBody($data);
        $str .= '</xml>';
        return $str;
    }

    /**
     * 生成xml body
     * @param $data
     * @return string
     */
    protected function makeXmlBody($data)
    {
        $xml = '';
        foreach ($data as $key => $item) {
            if (empty($item)) {
                continue;
            }
            if (is_array($item)) {
                $xml .= "<$key>" . $this->makeXmlBody($item) . "</$key>";
            } elseif (is_numeric($item)) {
                $xml .= "<$key>$item</$key>";
            } else {
                $xml .= "<$key><![CDATA[" . $item . "]]></$key>";
            }
        }
        return $xml;
    }

    /**
     * 解析xml字符串
     * @param $xmlStr
     * @return bool|mixed
     */
    public function xmlParse($xmlStr)
    {
        $xml_parser = xml_parser_create();
        libxml_disable_entity_loader(true);
        if (xml_parse($xml_parser, $xmlStr, true)) {
            return json_decode(json_encode(simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        } else {
            xml_parser_free($xml_parser);
            return false;
        }
    }
}