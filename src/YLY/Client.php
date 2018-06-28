<?php
/**
 * Created by PhpStorm.
 * User: mayunfeng
 * Date: 2018/2/22
 * Time: 14:25
 */

namespace Printer\YLY;

use Printer\Core\AbstractAPI;

class Client extends AbstractAPI
{

    const PRINT_INDEX = 'https://open-api.10ss.net/print/index'; //文本打印

    const PRINT_ADD = 'https://open-api.10ss.net/printer/addprinter'; //终端授权 (永久授权)

    const PRINT_DEL = 'https://open-api.10ss.net/printer/deleteprinter'; // 删除终端授权

    const SET_SOUND = 'https://open-api.10ss.net/printer/setsound'; // 声音调节接口

    /**
     *
     * @param array $params
     * @return \Mayunfeng\Supports\Collection
     */
    public function index($params=[])
    {
        return $this->parseJSON(self::POST,[self::PRINT_INDEX,$params]);
    }


    /**
     * @param array $params
     * @return \Mayunfeng\Supports\Collection
     */
    public function addPrinter($params=[])
    {
        return $this->parseJSON(self::POST,[self::PRINT_ADD,$params]);
    }


    public function setSound($params=[])
    {
        return $this->parseJSON(self::POST,[self::SET_SOUND,$params]);
    }


    public function delPrinter($params=[])
    {
        return $this->parseJSON(self::POST,[self::PRINT_DEL,$params]);
    }



}