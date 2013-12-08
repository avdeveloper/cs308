<?php

class DB {

    /**
     * This determines which database to query
     * @var Array
     */
    private $connection_settings = array(
        'host'      => 'localhost',
        'user'      => 'root',
        'password'  => 'Five51!',
        'database'  => 'calendar'
    );

    public function __construct() {
    }

    /**
     * Log a user into the system
     * 
     * @param Array $user An associative array containing the pid and password
     * @return Array the pid and default privacy of the person or EMPTY if not found
     */
    public function login_user($user) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = null;
        $sql = 'SELECT pid, d_privacy FROM person WHERE pid=? AND passwd=?';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('ss', $pid_param, $password_param);
            $pid_param = $user['pid'];
            $password_param = md5($user['password']);
            $statement->execute();
            $statement->bind_result($pid_result, $d_privacy_result);
            $statement->fetch();
            $returnable = array(
                'pid' => $pid_result,
                'd_privacy' => $d_privacy_result
            );
            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /**
     * Fetch all the events that a specific user is managing
     *
     * @param String $pid The user's id
     * @return Array all the events that the user is managing if found
     */
    public function get_managed_events($pid) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'SELECT  event.eid, event.start_time, ADDTIME(event.start_time, event.duration) as end_time,
                        event.description, COUNT(invited.pid) as attendees
                FROM event LEFT JOIN invited ON event.eid=invited.eid
                WHERE event.pid=? GROUP BY event.eid';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('s', $pid_param);
            $pid_param = $pid;
            $statement->execute();
            $statement->bind_result($eid, $start_time, $end_time, $description, $attendees);
            
            while ($statement->fetch()) {
                array_push($returnable, array(
                    'eid' => $eid,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'description' => $description,
                    'attendees' => $attendees
                ));
            }

            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /** 
     * Fetch all the events that a user has accepted for a specific date
     *
     * @param String $pid The user's id
     * @param String $date The date of the events to fetch
     * @return Array All the events the user has accepted to attend for a given day
     */
    public function get_events_for_date($pid, $date) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'SELECT invited.eid,
                       event.description,
                       event.start_time,
                       event.duration
                FROM `invited`
                     JOIN event ON invited.eid=event.eid
                     JOIN eventdate ON invited.eid=eventdate.eid
                WHERE invited.pid=? AND
                      invited.response=1 AND
                      eventdate.edate=?
                ORDER BY event.start_time';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('ss', $pid_param, $date_param);
            $pid_param = $pid;
            $date_param = $date;
            $statement->execute();
            $statement->bind_result($eid, $description, $start_time, $duration);
            
            while ($statement->fetch()) {
                array_push($returnable, array(
                    'eid' => $eid,
                    'description' => $description,
                    'start_time' => $start_time,
                    'duration' => $duration
                ));
            }

            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /** 
     * Fetch all the events that a user has accepted between two dates
     *
     * @param String $pid The user's id
     * @param String $from The date from which to start fetching events
     * @param String $to   The date to which we stop fetching events
     * @return Array All the events the user has accepted to attend for a given date range
     */
    public function get_events_between($pid, $from, $to) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'SELECT invited.eid,
                       event.description,
                       eventdate.edate,
                       event.start_time,
                       event.duration
                FROM `invited`
                     JOIN event ON invited.eid=event.eid
                     JOIN eventdate ON invited.eid=eventdate.eid
                WHERE invited.pid=? AND
                      eventdate.edate BETWEEN ? AND ? 
                ORDER BY eventdate.edate ASC';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('sss', $pid_param, $from_param, $to_param);
            $pid_param = $pid;
            $from_param = $from;
            $to_param = $to;
            $statement->execute();
            $statement->bind_result($eid, $description, $date, $start_time, $duration);
            
            while ($statement->fetch()) {
                array_push($returnable, array(
                    'eid' => $eid,
                    'description' => $description,
                    'date' => $date,
                    'start_time' => $start_time,
                    'duration' => $duration
                ));
            }

            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /**
     * Connect to the mysql database
     *
     * @param &Object $mysqli The object to make queries from when db is connected
     */
    private function connect_to_db(&$mysqli) {
        // Convert connection settings attributes to variables for this method
        foreach ($this->connection_settings as $key => $value) {
            $$key = $value;
        }

        // connect
        $mysqli = new mysqli($host, $user, $password, $database);
        return !!! mysqli_connect_errno();
    }
}
