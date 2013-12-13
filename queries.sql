// Log a user in
SELECT pid, d_privacy FROM person WHERE pid=? AND passwd=?

// Fetch events that the user manages
SELECT  event.eid, event.start_time,
        ADDTIME(event.start_time, event.duration) as end_time,
        event.description,
        COUNT(CASE WHEN invited.response=1 THEN 1 ELSE NULL END) as attendees
FROM event LEFT JOIN invited ON event.eid=invited.eid
WHERE event.pid=?
GROUP BY event.eid

// Find all events that a user has accepted for a specific date
SELECT invited.eid,
       event.description,
       event.start_time,
       event.duration
FROM `invited`
     JOIN event ON invited.eid=event.eid
     JOIN eventdate ON invited.eid=eventdate.eid
WHERE invited.pid=? AND
      invited.response=1 AND
      eventdate.edate=?
ORDER BY event.start_time

// Get user's pending invitations
SELECT event.eid,
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
WHERE invited.response=0 AND invited.pid=?

// Respond to an invitation
UPDATE invited SET response=?, visibility=?  WHERE pid=? AND eid=?

// Fetch the user's friends
SELECT person.pid,
       person.fname,
       person.lname
FROM friend_of
    LEFT JOIN person ON person.pid = friend_of.sharer
WHERE friend_of.viewer=?

// Get a friend's schedule
SELECT sharer.fname,
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
ORDER BY event.start_time ASC

// Fetch an event
SELECT *,
       event.pid=? as is_host
FROM event
WHERE event.eid=?

// Fetch a list of people to invite to the party
SELECT person.pid,
       person.fname,
       person.lname
FROM person
WHERE person.pid <> ? AND
      person.pid
NOT IN (
    SELECT invited.pid
    FROM invited
    WHERE invited.eid = ?)

// Invite someone
INSERT INTO invited (pid, eid, response, visibility)
VALUES (?,?,0,0);

// Create an event
INSERT INTO event (pid, start_time, duration, description)
    VALUES (?,?,?,?);
INSERT INTO eventdate (eid, edate)
    VALUES (?, ?);
