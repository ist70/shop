<?php

namespace App\Components\Video;

use App\Modules\Learning\Models\Video;
use T4\Mvc\Application;

class Youtube
    implements IProvider
{

    protected $model;
    protected $info;

    public function __construct(Video $model)
    {
        $this->model = $model;
    }

    public function sanitizeId($value)
    {
        if (preg_match('~watch\?v=([^&]+)~', $value, $m)) {
            return $m[1];
        };
        return $value;
    }

    protected function getInfo()
    {

        if (null === $this->info) {
            $apiKey = Application::getInstance()->config->googleapi->apikey;
            $url = 'https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id=' . $this->model->id . '&key=' . $apiKey;
            $videoContent = file_get_contents($url);
            $data = json_decode($videoContent);
            $this->info = $data->items[0]->contentDetails->duration;
        }
        return $this->info;
    }

    public function getDuration()
    {
        $datetime = new \DateTime('@0');
        $datetime->add(new \DateInterval($this->getInfo()));
        return $datetime->format('H:i:s');
    }

    public function getPlayer()
    {
        return <<<PLAYER
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{$this->model->id}" allowfullscreen></iframe>
        </div>
PLAYER;

    }
}