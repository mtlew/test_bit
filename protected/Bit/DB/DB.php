<?php
/**
 * Статический класс работы с базой данных
 * @author Mike Nastin <mike@luchsnenet.ru>
 */

namespace Bit\DB;


use Bit\DB\DBException;

class DB
{
    public static $linkArr         = array();
    public static $link            = null;
    public static $alias           = null;
    public static $log			   = false;
    public static $query     	   = '';
    public static $queryCount 	   = 0;
    public static $queryArray 	   = array();
    public static $queryCacheHit   = 0;
    public static $queryTime       = 0.0;

    /**
     * подключается к бд.
     * array
     * $params[type] - тип бд - пока только mysql
     * $params[host] - хост
     * $params[user] - юзер
     * $params[pass] - пароль
     * $params[db]	 - база, если пусто, то не выбирается
     * $params[port] - порт
     *
     * string
     * dbtype:user:pass:host:port:db:alias
     * @param array $params параметры подключения(host,user,pass,db)
     * @return resource|null
     */
    public static function connect($params = null)
    {
        global $memcache;
        if (!isset($memcache) && class_exists('Memcache', false))
        {
            $memcache = new Memcache;
            $result = @$memcache->connect('localhost', 11211);
            if($result == false)
                unset($memcache);
        }
        else
            unset($memcache);

        if (!$params)
            $db = $GLOBALS['CORE_DB'];
        elseif (is_array($params))
            $db = $params;
        elseif (is_string($params))
            list($db['type'],$db['user'],$db['pass'],$db['host'],$db['port'],$db['db'],$db['alias']) = explode(':',$params);

        switch ($db['type'])
        {
            case 'mysql':
                mysqli_connect($db['host'],$db['user'],$db['pass'],$db['db'],$db['port']);
                self::$link = self::$linkArr[$db['alias']] = mysqli_connect($db['host'],$db['user'],$db['pass'],$db['db'],$db['port']);
                self::$alias = $db['alias'];

                break;
            default:
                throw new DBException('unsupported db type');
                break;
        }
        if (!self::$link) {
            throw new DBException('cannt connect to db:'.$db['type']);
        }

        return true;
    }
    /**
     * возвращает ошибку MySQL
     *
     * @param int $type что именно вернуть(0-id,text;1-id;2-text)
     * @param bool $isArray как вернуть - в виде массива или строкой
     * @return array|string
     */
    public static function error($type = 1,$isArray = false)
    {
        if (!self::$link)
        {
            $id   = -1;
            $text = "link to base is null(".mysqli_connect_errno().":".mysqli_connect_error().")";
        }
        else
        {
            $id   = mysqli_errno(self::$link);
            $text = mysqli_error(self::$link);
        }
        switch ($type)
        {
            case 0://возвратить и id и text
                return $isArray ? array("id" => $id,"text"=>$text) : "$id : $text";
            case 1://возвратить id
            default:
                return $isArray ? array("id" => $id) : $id;
            case 2://возвратить text
                return $isArray ? array("text" => $text) : $text;
        }
    }
    /**
     * Выполняет запрос, возвращает resource# или null или кидает DBException
     * @param string $sqlText
     * @return resource|null|DBException
     */
    public static function query($query)
    {
        self::$query = $query;
        self::$queryCount++;
        if (!self::$link && !self::connect())
        {
            return null;
        }
        /**
         * Если разрешен замер времени исполнения запроса
         */
        if (defined('DB_QUERY_TIME')  && DB_QUERY_TIME == 'enable')
        {
            $_time1 = microtime(true);
        }

        $result = mysqli_query(self::$link, $query);
        if (DB::error() != 0)
        {
            throw new DBException('rise db error');
        }

        /**
         * Если разрешен замер времени исполнения запроса
         */
        if (defined('DB_QUERY_TIME')  && DB_QUERY_TIME == 'enable')
        {
            $_time2           = microtime(true);
            self::$queryTime  = $_time2 - $_time1;
            /**
             * Логирование долгих запросов
             */
            if (defined('DB_QUERY_SLOWLOG')               && //разрешено логирование
                DB_QUERY_SLOWLOG == 'enable'              && //разрешено логирование
                defined('DB_QUERY_SLOWLOG_FILE')          && //определен путь к файлу
                defined('DB_QUERY_SLOWLOG_TIME')          && //определено время при котором можно логировать
                DB_QUERY_SLOWLOG_TIME < self::$queryTime  && //долгий запрос?
                is_dir(dirname(DB_QUERY_SLOWLOG_FILE))    && //существует ли путь к файу
                is_writable(dirname(DB_QUERY_SLOWLOG_FILE))  // есть права на запись
            )
            {
                /**
                 * берем бектрейс
                 */
                $traceArray = debug_backtrace();
                $trace = '';
                foreach ($traceArray as $key => $row)
                {
                    $trace .= '#'.$key.' '.$row['file'].'('.$row['line'].'): ';
                    if (isset($row['class']))
                    {
                        $trace .= $row['class'].'->'.$row['function'].'(';
                    }
                    else
                    {
                        $trace .= $row['function'].'(';
                    }
                    if (isset($row['args']) && is_array($row['args']))
                    {
                        $args = array();
                        foreach ($row['args'] as $arg)
                        {
                            if (is_string($arg))
                            {
                                if (mb_strlen($arg, 'UTF-8') > 6)
                                {
                                    $arg  = str_replace("\n", " ", mb_substr($arg, 0, 6, 'UTF-8')).'..';
                                }
                                $args[] = "'".$arg."'";
                            }
                            elseif (is_array($arg))
                            {
                                $args[] = 'Array';
                            }
                        }
                        $trace .= implode(', ', $args);
                    }
                    $trace .= ")\n";
                }

                if (defined('DB_QUERY_SLOWLOG_TYPE') && DB_QUERY_SLOWLOG_TYPE == 'string')
                {
                    $_query = preg_replace('|[\s]+|iu', ' ', $query);
                }
                else
                {
                    $_query = $query;
                }

                $_data = "#[".date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'])."]"
                    . " alias:'" . self::$alias . "' time: ".self::$queryTime."\n"
                    . $trace."#\n"
                    . trim($_query)."\n"
                    . "\n";
                file_put_contents(DB_QUERY_SLOWLOG_FILE, $_data, FILE_APPEND);
                @chmod(DB_QUERY_SLOWLOG_FILE, 0666);
            }
        }
        if (defined('DB_QUERY_ARRAY') && DB_QUERY_ARRAY == 'enable')
        {
            self::$queryArray[] = array('query'   => $query,
                'time'    => self::$queryTime);
        }
        return $result;
    }
    /**
     * делает запрос к базе MySQL и обрабатывает ответ
     * если запрос такой, что использовать LIMIT нельзя или неполучается, то можно использовать $once
     * он может быть int значения:0 равнозначно true, > 0 - по порядку следования(начиная с 1 записи)
     * < 0 с обратного конца. 1 - взять первую запись, -1 -взять последнюю запись, -2 предпоследнюю
     *
     * при ERROR - $once = true возвращает строку, false - возврашает массив
     * при INSERT - $once = true возвращает id вставленного, false - возврашает количество рядов
     *
     * @param string   $sqlText        SQL запрос любого плана
     * @param mix      $once           брать одну запись или нет (0 !== false)
     * @param mix      $field          какие поля выбрать из запроса,
     *                                 null    - все,
     *                                 array   - указать поля (последовательность останется)
     *                                 string  - только одно поле
     * @param array    $group          группировка
     * @param bool     $containedGroup тоже полезна весщь
     * @param int      $mysqlRowType   MYSQLI_ASSOC | MYSQLI_NUM | MYSQLI_BOTH
     * @return array|result итог работы
     */
    public static function parse($sqlText,$once = false,$field = null,$group=null, $containedGroup = false, $mysqlRowType = MYSQLI_ASSOC)
    {
        if (!($result = self::query($sqlText))) return $result;

        /**
         * @todo чтонить сделать с этой хуйней - не нравиться мне
         */
        preg_match("/^SELECT|^SHOW|^DESCRIBE|^EXPLAIN|^DELETE|^INSERT|^REPLACE|^UPDATE/",strtoupper(substr(ltrim($sqlText),0,10)),$a);
        switch (isset($a[0]) ? "".$a[0] : "")
        {
            case "INSERT":
                if ($once) return mysqli_insert_id(self::$link);
            case "DELETE":
            case "REPLACE":
            case "UPDATE":
                //для этой группы можно вернуть количество затронутых рядов и все, парсить ничего не надо.
                return mysqli_affected_rows(self::$link);
            case "SELECT":
            case "SHOW":
            case "DESCRIBE":
            case "EXPLAIN":
                //для этой группы только и можно производить дальнейшую обработку, так как у всех остальных $result = TRUE
                if (!mysqli_num_rows($result)) return null;
                break;
            default:
                return $result;
        }
        if (0 === $once) $once = true;
        if ($once)
        {
            if (is_int($once))
            {
                $ret = mysqli_affected_rows(self::$link);
                if ($once > 0)
                {
                    $ret = $once > $ret ? 0 : $once;
                }
                else
                {
                    $ret = -$once > $ret ? 0 : $ret + $once + 1;
                }

                if (0 != $ret) $ret -= 1;
                mysqli_data_seek($result,$ret);
            }
            $row = mysqli_fetch_array($result,$mysqlRowType);
            return ($field == null) ? $row : $row[$field];
        }

        $ret = array();
        $group = is_string($group) ? array($group) : $group;
        $containedGroup = ($group) ? $containedGroup : true;
        $groupCount = count($group);

        while ($row = mysqli_fetch_array($result,$mysqlRowType))
        {
            /**
             * подготавливаем место($place), куда поставить новую порцию данных
             */
            $place =& $ret;

            for ($i=0; $i < $groupCount; $i++)
            {
                $place[$row[$group[$i]]] = (isset($place[$row[$group[$i]]])) ? $place[$row[$group[$i]]] : array();
                $place =& $place[$row[$group[$i]]];
            }

            /**
             * место готово ($place) - теперь определяем, что оставить от строки($row) из БД
             * typeof $field = array|string|null
             */
            if (is_string($field) && isset($row[$field]))
            {
                $add = $row[$field];
            }
            elseif (is_array($field))
            {
                foreach ($field as $needField)
                {
                    if (isset($row[$needField]))
                    {
                        $add[$needField] = $row[$needField];
                    }
                }
            }
            else
            {
                $add = $row;
            }

            if ($containedGroup)
                $place[] = $add;
            else
                $place = $add;
        }
        return $ret;
    }
    /**
     * Обертка для функции DB::parse с механизмом кэширования в memcache
     *
     * @param unknown_type $cachingTime - время кэширования
     * @param unknown_type $sqlText
     * @param unknown_type $once
     * @param unknown_type $field
     * @param unknown_type $group
     * @param unknown_type $containedGroup
     * @param unknown_type $mysqlRowType
     * @return unknown
     */
    function parseCache($cachingTime,$sqlText,$once = false,$field = null,$group=null, $containedGroup = false, $mysqlRowType = MYSQLI_ASSOC)
    {
        global $memcache;

        if (($normalizedCachingTime = intval($cachingTime)) == 0 || empty($memcache))
            return DB::parse($sqlText,$once ,$field,$group, $containedGroup, $mysqlRowType);

        $hashCode = md5($sqlText.$once.$field.serialize($group).$containedGroup.$mysqlRowType);

        if (($cachedData = $memcache->get($hashCode)) !== false)
        {
            // cache hit
            self::$queryCacheHit++;
            return unserialize($cachedData);
        }
        else
        {
            $processedData = DB::parse($sqlText, $once ,$field,$group, $containedGroup, $mysqlRowType);
            $memcache->set($hashCode,serialize($processedData),0,$normalizedCachingTime);
            return $processedData;
        }
    }

    function parseCacheFile($cachingTime, $sqlText,$once = false,$field = null,$group=null, $containedGroup = false, $mysqlRowType = MYSQLI_ASSOC)
    {
        $pathToCache = CORE_PATH_TO_PROJECT.'cache/';

        $hashCode = md5($sqlText.$once.$field.serialize($group).$containedGroup.$mysqlRowType);

        $file = $pathToCache.$hashCode.'.log';
        if ($cachingTime < 1)
            $cachingTime = 86400;
        if (file_exists($file))
        {
            $mTime = filemtime($file);
            if (($_SERVER['REQUEST_TIME'] - $mTime) < $cachingTime) // use cache
                $result = unserialize(file_get_contents($file));
            else
            {
                $result = DB::parse($sqlText,$once ,$field,$group, $containedGroup, $mysqlRowType);
                if ($fp = fopen($file, 'w'))
                {
                    fwrite($fp, serialize($result));
                    fclose($fp);
                }
            }
        }
        else
        {
            $result = DB::parse($sqlText, $once ,$field,$group, $containedGroup, $mysqlRowType);
            if ($fp = fopen($file, 'w'))
            {
                fwrite($fp, serialize($result));
                fclose($fp);
            }
        }
        return $result;
    }

    /**
     * Аналог self::parse(), только с возможностью выбора алиаса базы
     *
     * @param string $linkAlias
     * @param string $sqlText SQL запрос любого плана
     * @param bool|int $once брать одну запись или нет
     * @param string $field какие поля выбрать из запроса, null - все
     * @param array $group группировка
     * @param bool $containedGroup тоже полезна весщь
     * @param int $mysqlRowType
     * @return array|result итог работы
     */
    public static function parseFrom($linkAlias, $sqlText,$once = false,$field = null,$group=null, $containedGroup = false, $mysqlRowType = MYSQL_ASSOC)
    {
        self::setDBAlias($linkAlias);
        return self::parse($sqlText, $once, $field, $group, $containedGroup, $mysqlRowType);
    }
    /**
     * Меняет алиас к базе
     *
     * @param string $linkAlias
     */
    public static function setDBAlias($linkAlias)
    {
        if (isset(self::$linkArr[$linkAlias]) && self::$link !== self::$linkArr[$linkAlias])
        {
            self::$link  =& self::$linkArr[$linkAlias];
            self::$alias = $linkAlias;
        }
    }
    /**
     * Обертка для mysql_real_escape_string
     * @param string $query
     * @return string
     */
    public static function escape($query)
    {
        if (!self::$link)
        {
            self::connect();
            if (!(self::$link))	return null;
        }
        return mysqli_real_escape_string(self::$link,$query);
    }

    /**
     * Закрыть соединение с БД
     *
     */
    public static function close()
    {
        @mysqli_close(self::$link);
    }

    /**
     * Когда необходимо вернуть значения в list
     *
     * @param string $query
     * @return mix
     */
    public function toList($query)
    {
        return self::parse($query, true, null, null, false, MYSQLI_NUM);
    }
}