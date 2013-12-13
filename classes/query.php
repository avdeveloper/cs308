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
                        event.description, COUNT(CASE WHEN invited.response=1 THEN 1 ELSE NULL END) as attendees
                FROM event LEFT JOIN invited ON event.eid=invited.eid
                WHERE event.pid=?
                GROUP BY event.eid';

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
     * Fetch pending invitations that user has to respond to
     *
     * @param String $pid The user's id
     * @return Array All pending invitations the user has to respond to
     */
    public function get_pending_invitations($pid) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'SELECT event.eid,
                       person.fname as host_fname,
                       person.lname as host_lname,
                       event.description,
                       eventdate.edate,
                       event.start_time,
                       event.duration
                FROM invited
                     LEFT JOIN eventdate ON invited.eid=eventdate.eid
                     LEFT JOIN event ON eventdate.eid=event.eid
                     LEFT JOIN person ON event.pid=person.pid
                WHERE invited.response=0 AND invited.pid=?';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('s', $pid_param);
            $pid_param = $pid;
            $statement->execute();
            $statement->bind_result($eid, $host_fname, $host_lname, $description, $date, $start_time, $duration);
            
            while ($statement->fetch()) {
                array_push($returnable, array(
                    'eid'           => $eid,
                    'host_fname'    => $host_fname,
                    'host_lname'    => $host_lname,
                    'description'   => $description,
                    'date'          => $date,
                    'start_time'    => $start_time,
                    'duration'      => $duration
                ));
            }

            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /** 
     * Respond to an invitation
     *
     * @param String $pid The user id
     * @param String $eid The event id
     * @param Integer $response 0: decline 1: accept
     * @param Integer $visibility Determines at how much level is this visible to others
     * @return Array All pending invitations the user has to respond to
     */
    public function respond_to_invitation($pid, $eid, $response, $visibility) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'UPDATE invited SET response=?, visibility=?  WHERE pid=? AND eid=?';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('ddsd', $response_param, $visibility_param, $pid_param, $eid_param);
            $pid_param = $pid;
            $eid_param = $eid;
            $response_param = $response;
            $visibility_param = $visibility;
            $statement->execute();
            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /**
     * List the user's friends
     *
     * @param String $pid The user's id
     * @return Array a list of the user's friends name and id
     */
    public function get_friends($pid) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'SELECT person.pid,
                       person.fname,
                       person.lname
                FROM friend_of
                    LEFT JOIN person ON person.pid = friend_of.sharer
                WHERE friend_of.viewer=?';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('s', $pid_param);
            $pid_param = $pid;
            $statement->execute();
            $statement->bind_result($pid, $fname, $lname);
            
            while ($statement->fetch()) {
                array_push($returnable, array(
                    'pid'   => $pid,
                    'fname' => $fname,
                    'lname' => $lname,
                ));
            }

            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /**
     * Get a friend's schedule at a specific date
     *
     * @param String $pid         The user's id
     * @param String $friend_pid  The friend's id
     * @param String $date        The date the user wants to view
     */
    public function get_friend_schedule($pid, $friend_pid, $date) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'SELECT sharer.fname,
                       sharer.lname,
                       invited.visibility,
                       friend_of.level,
                       event.start_time,
                       event.duration,
                       event.description
                FROM friend_of
                    LEFT JOIN invited ON invited.pid = friend_of.sharer
                    LEFT JOIN eventdate ON eventdate.eid = invited.eid
                    LEFT JOIN event ON event.eid = invited.eid
                    LEFT JOIN person AS sharer ON sharer.pid = friend_of.sharer
                WHERE invited.response =1
                    AND friend_of.viewer = ?
                    AND friend_of.sharer = ?
                    AND eventdate.edate =  ?
                ORDER BY event.start_time ASC';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('sss', $pid_param, $friend_pid_param, $date_param);
            $pid_param = $pid;
            $friend_pid_param = $friend_pid;
            $date_param = $date;
            $statement->execute();
            $statement->bind_result($fname, $lname, $visibility, $level, $start_time, $duration, $description);

            while ($statement->fetch()) {
                array_push($returnable, array(
                    'fname'   => $fname,
                    'lname'   => $lname,
                    'visibility' => $visibility,
                    'level' => $level,
                    'start_time'   => $start_time,
                    'duration' => $duration,
                    'description' => $description,
                ));
            }

            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /**
     * Get details for a specific event
     *
     * @param String  $pid The user's id
     * @param Integer $eid The event id
     * @return Array the details of the event
     */
    public function get_event($pid, $eid) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'SELECT *,
                       event.pid=? as is_host
                FROM event
                WHERE event.eid=?';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('sd', $pid_param, $eid_param);
            $pid_param = $pid;
            $eid_param = $eid;
            $statement->execute();
            $statement->bind_result($eid, $start_time, $duration, $description, $pid, $is_host);
            $statement->fetch();
            $returnable = array(
                'eid'   => $eid,
                'start_time'   => $start_time,
                'duration' => $duration,
                'description' => $description,
                'pid'   => $pid,
                'is_host' => $is_host
            );

            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /**
     * Get the list of people to invite to the party
     *
     * @param String $pid The user id
     * @param String $eid The event id
     * @return Array The set of people to invite
     */
    public function get_people_to_invite($pid, $eid) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'SELECT person.pid,
                       person.fname,
                       person.lname
                FROM person
                WHERE person.pid <> ? AND
                      person.pid
                NOT IN (
                    SELECT invited.pid
                    FROM invited
                    WHERE invited.eid = ?)';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('sd', $pid_param, $eid_param);
            $pid_param = $pid;
            $eid_param = $eid;
            $statement->execute();
            $statement->bind_result($pid, $fname, $lname);

            while ($statement->fetch()) {
                array_push($returnable, array(
                    'pid'   => $pid,
                    'fname' => $fname,
                    'lname' => $lname,
                ));
            }

            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /**
     * Invite a person to an event
     *
     * @param String $pid The person to invite
     * @param Integer $eid The event to invite them to
     */
    public function invite($pid, $eid) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'INSERT INTO invited (pid, eid, response, visibility)
                VALUES (?,?,0,0);';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('sd', $pid_param, $eid_param);
            $pid_param = $pid;
            $eid_param = $eid;
            $statement->execute();
            $statement->close();
        }

        $mysqli->close();
        return $returnable;
    }

    /**
     * Create a new event with dates as well
     *
     * @param Array $event The form details
     */
    public function create_event($event) {
        if (! $this->connect_to_db($mysqli)) return false;

        $returnable = array();
        $sql = 'INSERT INTO event (pid, start_time, duration, description)
                VALUES (?,?,?,?);';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('ssss', $pid_param, $start_time_param, $duration_param, $description_param);
            $pid_param = $event['pid'];
            $start_time_param = $event['start_time'];
            $duration_param = $event['duration'];
            $description_param = $event['description'];
            $statement->execute();
            $statement->close();
            $event_id = $mysqli->insert_id;
        }

        $sql = 'INSERT INTO eventdate (eid, edate)
                VALUES (?, ?);';

        if ($statement = $mysqli->prepare($sql)) {
            $statement->bind_param('ds', $eid_param, $edate_param);
            
            foreach(explode(',', $event['dates']) as $date) {
                $eid_param = $event_id;
                $edate_param = trim($date);
                $statement->execute();
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
