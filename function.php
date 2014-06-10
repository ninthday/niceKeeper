<?php

/*
  yourTwapperKeeper - Twitter Archiving Application - http://your.twapperkeeper.com
  Copyright (c) 2010 John O'Brien III - http://www.linkedin.com/in/jobrieniii

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

class YourTwapperKeeper {

// sanitize data
    function sanitize($input) {
        if (is_array($input)) {
            foreach ($input as $k => $i) {
                $output[$k] = $this->sanitize($i);
            }
        } else {
            if (get_magic_quotes_gpc()) {
                $input = stripslashes($input);
            }
            $output = mysql_real_escape_string($input);
        }
        return $output;
    }

// list archives
    function listArchive($id = false, $keyword = false, $description = false, $tags = false, $screen_name = false, $debug = false) {
        global $db;

        $q = "SELECT * FROM `archives` WHERE 1";

        if ($id) {
            $q .= " and id = '$id'";
        }

        if ($keyword) {
            $q .= " and `keyword like` '%$keyword%'";
        }

        if ($description) {
            $q .= " and `description` like '%$description%'";
        }

        if ($tags) {
            $q .= " and `tags` like '%$tags%";
        }

        if ($screen_name) {
            $q .= " and `screen_name` like '%$screen_name%";
        }
        // @ninthday: Order by id.
        $q .= " ORDER BY `archives`.`id` DESC";

        $r = mysql_query($q, $db->connection);

        $count = 0;
        while ($row = mysql_fetch_assoc($r)) {
            $count++;
            $response['results'][] = $row;
        }

        $response['count'] = $count;

        return $response;
    }

// create archive
    function createArchive($keyword, $description, $tags, $screen_name, $user_id, $debug = false) {
        global $db;
        $q = "select * from archives where keyword = '$keyword'";
        $r = mysql_query($q, $db->connection);
        if (mysql_num_rows($r) > 0) {
            $response[0] = "Archive for that keyword / hashtag already exists.";
            return($response);
        }

        if (strlen($keyword) < 1 || strlen($keyword) > 30) {
            $response[0] = "Keyword / hashtag cannot be blank";
            return($response);
        }

        if (strlen($keyword) > 30) {
            $response[0] = "Keyword / hashtag must be less than 30 characters.";
            return($response);
        }

        $q = "insert into archives values ('','$keyword','$description','$tags','$screen_name','$user_id','','" . $this->getNowTime() . "')";
        $r = mysql_query($q, $db->connection);
        $lastid = mysql_insert_id();

        $create_table = "CREATE TABLE IF NOT EXISTS `z_$lastid` (
        `archivesource` varchar(100) NOT NULL,
        `text` varchar(1000) NOT NULL,
        `to_user_id` varchar(100) NOT NULL,
        `from_user` varchar(100) NOT NULL,
        `id` varchar(100) NOT NULL,
        `from_user_id` varchar(100) NOT NULL,
        `iso_language_code` varchar(10) NOT NULL,
        `source` varchar(250) NOT NULL,
        `profile_image_url` varchar(250) NOT NULL,
        `geo_type` varchar(30) NOT NULL,
        `geo_coordinates_0` double NOT NULL,
        `geo_coordinates_1` double NOT NULL,
        `created_at` varchar(50) NOT NULL,
        `time` int(11) NOT NULL,
        UNIQUE KEY `id` (`id`),
        FULLTEXT `full` (`text`),
        INDEX `source` (`from_user`),
        INDEX `from_user` (`from_user`),
        INDEX `iso_language_code` (`iso_language_code`),
        INDEX `geo_type` (`geo_type`),
        INDEX `time` (`time`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

        $r = mysql_query($create_table, $db->connection);

        $response[0] = "Archive has been created.";
        return($response);
    }

// get tweets
    function getTweets($id, $start = false, $end = false, $limit = false, $orderby = false, $nort = false, $from_user = false, $text = false, $lang = false, $max_id = false, $since_id = false, $offset = false, $lat = false, $long = false, $rad = false, $debug = false) {
        global $db;

        $response = array();
        $type = $this->sanitize($type);
        $name = $this->sanitize($name);
        $start = $this->sanitize($start);
        $end = $this->sanitize($end);
        $limit = $this->sanitize($limit);
        $orderby = $this->sanitize($orderby);
        $nort = $this->sanitize($nort);
        $from_user = $this->sanitize($from_user);
        $text = $this->sanitize($text);
        $lang = $this->sanitize($lang);
        $offset = $this->sanitize($offset);
        $max_id = $this->sanitize($max_id);
        $since_id = $this->sanitize($since_id);
        $lat = $this->sanitize($lat);
        $long = $this->sanitize($long);
        $rad = $this->sanitize($rad);

        $q = "select * from z_" . $id . " where 1";

        // build param query
        $qparam = '';

        if ($start > 0) {
            $qparam .= " and time > $start";
        }

        if ($end > 0) {
            $qparam .= " and time < $end";
        }

        if ($nort == 1) {
            $qparam .= " and text not like 'RT%'";
        }

        if ($from_user) {
            $qparam .= " and from_user = '$from_user'";
        }

        if ($text) {
            $qparam .= " and text like '%$text%'";
        }

        if ($lang) {
            $qparam .= " and iso_language_code='$lang'";
        }

        if ($since_id) {
            $qparam .= " and id >= $since_id";
        }

        if ($max_id) {
            $qparam .= " and id <= $max_id";
        }

        if ($lat OR $long OR $rad) {

            $R = 6371;  // earth's radius, km

            $maxLat = $lat + rad2deg($rad / $R);
            $minLat = $lat - rad2deg($rad / $R);

            $maxLon = $lon + rad2deg($rad / $R / cos(deg2rad($lat)));
            $minLon = $lon - rad2deg($rad / $R / cos(deg2rad($lat)));

            $qparam .= " and geo_coordinates_0 > $minLat and geo_coordinates_0 < $maxLat and geo_coordinates_1 > $minLon and geo_coordinates_1 < $maxLon";
        }

        if ($orderby == "a") {
            $qparam .= " order by time asc";
        } else {
            $qparam .= " order by time desc";
        }

        if ($limit) {
            $qparam .= " limit $limit";
        }

        $query = $q . $qparam;

        $r = mysql_query($query, $db->connection);

        $response = array();
        while ($row = mysql_fetch_assoc($r)) {
            $response[] = $row;
        }
        return $response;
    }

// delete archive
    function deleteArchive($id) {
        global $db;
        $q = "delete from archives where id = '$id'";
        $r = mysql_query($q, $db->connection);

        $q = "drop table if exists z_$id";
        $r = mysql_query($q, $db->connection);

        $response[0] = "Archive has been deleted.";
        return($response);
    }

// update archive
    function updateArchive($id, $description, $tags) {
        global $db;
        $q = "update archives set description = '$description' where id = '$id'";
        $r = mysql_query($q, $db->connection);
        $q = "update archives set tags = '$tags' where id = '$id'";
        $r = mysql_query($q, $db->connection);
        $response[0] = "Archive has updated.";
        return($response);
    }

    /**
     * Check status of archiving processes
     * 
     * @global type $db
     * @param array $process_array
     * @return array [0]=>(bool)runing state, [1]=>(array)pids, [2]=>State content
     * @version nicekeeper v.0.1.2
     */
    function statusArchiving($process_array) {
        global $db;
        // If PIDs > 0 - we are considered running
        $running = TRUE;
        $pids = '';
        $pids_array = array();
        $shouldBeRunning = 1;
        $process_string = implode('\', \'', $process_array);
        $sql = 'SELECT * FROM `processes` WHERE `process` IN (\'' . $process_string . '\')';
        $rs = mysql_query($sql, $db->connection);

        while ($row = mysql_fetch_assoc($rs)) {
            $pids_array[] = $row['pid'];
            exec('ps ' . $row['pid'], $PROC);
            if (count($PROC) < 2) {
                $running = FALSE;
            }
            if ($row['pid'] == 0) {
                $running = FALSE;
                $shouldBeRunning = FALSE;
            }
        }
        /**
          foreach ($process_array as $key => $value) {
          $q = "select * from processes where process = '$value'";
          $r = mysql_query($q, $db->connection);
          $r = mysql_fetch_assoc($r);
          $pid = $r['pid'];
          exec("ps $pid", $PROC);

          if (count($PROC) < 2) {
          $running = FALSE;
          }
          $pids .= $pid . ",";
          if ($pid == 0) {
          $running = FALSE;
          $shouldBeRunning = FALSE;
          }
          }
          $pids = substr($pids, 0, -1);
         * */
        $rtn_array = array();
        if ($running == FALSE) {
            $rtn_array[0] = FALSE;
            $rtn_array[1] = $pids_array;
            if ($shouldBeRunning == 1) {
                $rtn_array[2] = "Archiving processes have died.";
            } else {
                $rtn_array[2] = "Archiving processes are NOT running.";
            }
        } else {
            $rtn_array[0] = TRUE;
            $rtn_array[1] = $pids_array;
            $rtn_array[2] = "Archiving processes are running.";
        }

        return $rtn_array;
    }

// kill archiving process
    function killProcess($pid) {
        $command = 'kill -9 ' . $pid;
        exec($command);
    }

// start archiving process
    function startProcess($cmd) {
        $command = "$cmd > /dev/null 2>&1 & echo $!";
        exec($command, $op);
        $pid = (int) $op[0];
        return ($pid);
    }

    /**
     * Save Archive to saved_archives table
     * 
     * @param int $id Archive ID
     * @return array [0]=>(bool)Successful or NOT, [1]=>Return message.
     * @author Ninthday <jeffy@ninthday.info>
     * @since nicekeeper v.0.1.3
     */
    public function saveArchive($id) {
        global $db;
        $rtn = array();
        $sql = 'INSERT INTO `saved_archives` '
                . 'SELECT `id`, `keyword`, `description`, `tags`, `screen_name`, `user_id`, `count`, `create_time`, NOW() '
                . 'FROM `archives` WHERE `id`=' . $id;
        $rs = mysql_query($sql, $db->connection);
        if (!$rs) {
            $rtn[0] = FALSE;
            $rtn[1] = 'It has problem in Save Archive to table.';
        } else {
            $sql = 'DELETE FROM `archives` WHERE `id`=' . $id;
            $rs = mysql_query($sql, $db->connection);
            if (!$rs) {
                $rtn[0] = FALSE;
                $rtn[1] = 'It has problem in Delete from Archive table.';
            } else {
                $rtn[0] = TRUE;
                $rtn[1] = 'Archive has saved.';
            }
            $rtn[0] = TRUE;
            $rtn[1] = 'Archive has saved.';
        }
        return $rtn;
    }

    /**
     * Get Now Time String with MySQL DATETIME format
     * 
     * @author Ninthday <jeffy@ninthday.info>
     * @return string MySQL Datetime format
     * @since nicekeeper v.0.1.3
     */
    public function getNowTime() {
        return date('Y-m-d H:i:s');
    }

    /**
     * list of Saved Archive
     * 
     * @global type $db
     * @param string $id
     * @param string $keyword
     * @param string $description
     * @param string $tags
     * @param string $screen_name
     * @param boolen $debug
     * @return array
     */
    public function listSavedArchive($id = false, $keyword = false, $description = false, $tags = false, $screen_name = false, $debug = false) {
        global $db;

        $sql = 'SELECT * FROM `saved_archives` WHERE 1';

        if ($id) {
            $sql .= ' AND `id` = \'' . $id . '\'';
        }

        if ($keyword) {
            $sql .= ' AND `keyword` LIKE \'' . $keyword . '%\'';
        }

        if ($description) {
            $sql .= ' AND `description` LIKE \'%' . $description . '%\'';
        }

        if ($tags) {
            $sql .= ' AND `tags` LIKE \'%' . $tags . '%\'';
        }

        if ($screen_name) {
            $sql .= ' AND `screen_name` LIKE \'%' . $screen_name . '%\'';
        }


        $rs = mysql_query($sql, $db->connection);

        $count = 0;
        while ($row = mysql_fetch_assoc($rs)) {
            $count++;
            $response['results'][] = $row;
        }

        $response['count'] = $count;

        return $response;
    }

    /**
     * Create event 
     * 
     * @global type $db
     * @param string $title
     * @param string $description
     * @param date $event_time
     * @param string $screen_name
     * @return array
     */
    public function createEvent($title, $description, $event_time, $screen_name) {
        global $db;
        $rtn = array();
        $sql = 'INSERT INTO `event`(`e_title`, `e_description`, `e_event_time`, `screen_name`, `e_create_time`) '
                . 'VALUES (\'' . $title . '\', \'' . $description . '\',\'' . $event_time . '\', \'' . $screen_name . '\', \'' . $this->getNowTime() . '\')';
        $rs = mysql_query($sql, $db->connection);
        if (!$rs) {
            $rtn[0] = FALSE;
            $rtn[1] = 'It has a problem in Save Event to table.';
        } else {
            $rtn[0] = TRUE;
            $rtn[1] = 'Event has been created.';
        }
        return $rtn;
    }
    /**
     * Counting rows during two weeks. Return by day
     * @global type $db
     * @param int $archive_id
     * @return array [0]:TRUE or FALSE [1]: count string
     */
    public function countRowByID($archive_id){
        global $db;
        $rtn = array();
        $sql ='SELECT FROM_UNIXTIME(`time`, \'%Y-%m-%d\') AS `MYDATE`, COUNT(*) AS `CTN` FROM `z_' . $archive_id . '` 
            WHERE `time` > UNIX_TIMESTAMP(CONCAT(DATE_SUB(CURDATE(), INTERVAL 14 DAY), \' 23:59:59\'))
            GROUP BY `MYDATE`
            ORDER BY `MYDATE`';
        $rs = mysql_query($sql, $db->connection);
        if (!$rs) {
            $rtn[0] = FALSE;
            $rtn[1] = 'It has a problem in count how many ROWS during this two weeks.';
        } else {
            $rtn[0] = TRUE;
            $aryCount = array();
            while ($row = mysql_fetch_assoc($rs)) {
                array_push($aryCount, $row['CTN']);
            }
            $rtn[1] = implode(', ', $aryCount);
        }
        return $rtn;
    }

    /**
     * Add archive to Event.
     * 
     * @global type $db
     * @param int $event_id
     * @param int $archive_id
     * @return array
     */
    public function addEARelation($event_id, $archive_id) {
        global $db;
        $rtn = array();
        $sql = 'INSERT INTO `event_archive`(`e_id`, `a_id`) VALUES (' . $event_id . ', ' . $archive_id . ')';
        $rs = mysql_query($sql, $db->connection);
        if (!$rs) {
            $rtn[0] = FALSE;
            $rtn[1] = 'It has problem in Add Archive to Event.';
        }else{
            $rtn[0] = TRUE;
            $rtn[1] = 'Archive has add to Event.';
        }
        return $rtn;
    }
    
    public function listUserEvent($screen_name=''){
        global $db;
        $rtn = array();
        $sql = 'SELECT * FROM `event` WHERE `screen_name` = \'' . $screen_name . '\'';
        $rs = mysql_query($sql, $db->connection);
        
        $count = 0;
        while ($row = mysql_fetch_assoc($rs)) {
            $count++;
            $rtn['results'][] = $row;
        }

        $rtn['count'] = $count;
        return $rtn;
    }
    
}

$tk = new YourTwapperKeeper;
?>
