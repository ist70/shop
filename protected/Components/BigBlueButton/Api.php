<?php

namespace App\Components\BigBlueButton;

use App\Models\User;
use App\Modules\Meetings\Models\Meeting;
use T4\Mvc\Application;

class Api
{

    protected $url;
    protected $secret;

    public function __construct()
    {
        $config = Application::getInstance()->config->bigbluebutton;
        $this->url = $config->url;
        $this->secret = $config->secret;
    }

    public function getMeetings()
    {
        return $this->sendRequest('getMeetings');
    }

    public function create($id, $name = '')
    {
        $params = [];
        $params['meetingID'] = $id;
        if (!empty($name)) {
            $params['name'] = $name;
        }
        $params['record'] = 'true';
        $params['autoStartRecording'] = 'true';

        return $this->sendRequest('create', $params);
    }

    public function getJoinUrl(Meeting $meeting, User $user, $type = 'attendee')
    {
        return $this->buildQuery('join', [
            'meetingID' => $meeting->id,
            'fullName' => $user->fullName,
            'userID' => $user->getPk(),
            'password' => 'moderator' == $type ? $meeting->moderatorPW : $meeting->attendeePW,
        ]);
    }

    public function end($id, $password)
    {
        return $this->sendRequest('end', [
            'meetingID' => $id,
            'password' => $password,
        ]);
    }

    protected function buildQuery($call, array $params = [])
    {
        if (!empty($params)) {
            $request = http_build_query($params);
            $checksum = sha1($call . $request . $this->secret);
            $request .= '&checksum=' . $checksum;
        } else {
            $checksum = sha1($call . $this->secret);
            $request = 'checksum=' . $checksum;
        }
        return $this->url . '/api/' . $call . '?' . $request;
    }

    /**
     * @param string $call
     * @param array $params
     * @return \SimpleXMLElement
     * @throws \App\Components\BigBlueButton\Exception
     */
    protected function sendRequest($call, $params = [])
    {
        $url = $this->buildQuery($call, $params);
        $result = file_get_contents($url);
        if (false === $result) {
            throw new Exception($url . ' is unreachable');
        }

        $xml = simplexml_load_string($result);
        if (false === $xml) {
            throw new Exception('Response from URL ' . $url . ' is not valid XML');
        }
        if ($xml->returncode == 'SUCCESS') {
            return $xml;
        } else {
            throw new Exception($result);
        }
    }

}