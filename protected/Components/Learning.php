<?php

namespace App\Components;

use App\Models\User;
use App\Modules\Learning\Models\Course;
use App\Modules\Learning\Models\Lesson;
use App\Modules\Learning\Models\UserLearningLog;

class Learning
{

    /**
     * @param \App\Models\User $user
     * @param \App\Modules\Learning\Models\Course $course
     * @return \App\Components\Learning
     */
    public static function instance(User $user, Course $course)
    {
        static $instances = [];
        if (!isset($instances[$user->getPk()][$course->getPk()])) {
            $instances[$user->getPk()][$course->getPk()] = new self($user, $course);
        }
        return $instances[$user->getPk()][$course->getPk()];
    }

    /**
     * @var \App\Models\User
     */
    protected $user;

    /**
     * @var \App\Modules\Learning\Models\Course
     */
    protected $course;

    /**
     * @var \T4\Core\Collection|\App\Modules\Learning\Models\UserLearningLog[]
     */
    protected $log;

    protected function __construct(User $user, Course $course)
    {
        $this->user = $user;
        $this->course = $course;
        $this->log = UserLearningLog::findAllByUserAndCourse($user, $course);
    }

    public function getLessonsCount()
    {
        return $this->course->lessons->count();
    }

    public function getCurrentLesson()
    {
        $log = $this->log;
        $openedLessons = $this->course->lessons->filter(function (Lesson $lesson) use ($log) {
            return
                $log->existsElement([
                    '__lesson_id' => $lesson->getPk(),
                    'step' => UserLearningLog::STEPS['LESSON'],
                    'stage' => UserLearningLog::STAGES['STARTED'],
                ])
                &&
                !$log->existsElement([
                    '__lesson_id' => $lesson->getPk(),
                    'step' => UserLearningLog::STEPS['LESSON'],
                    'stage' => UserLearningLog::STAGES['FINISHED'],
                ]);

        });
        if ($openedLessons->isEmpty()) {
            return $this->course->lessons[0];
        } else {
            return $openedLessons[0];
        }
    }

    public function getFinishedLessons()
    {
        $log = $this->log;
        return $this->course->lessons->filter(function (Lesson $lesson) use ($log) {
            return $log->existsElement([
                '__lesson_id' => $lesson->getPk(),
                'step' => UserLearningLog::STEPS['LESSON'],
                'stage' => UserLearningLog::STAGES['FINISHED'],
            ]);
        });
    }

    public function getFinishedLessonsCount()
    {
        return $this->getFinishedLessons()->count();
    }

    public function getNextLessons()
    {
        $log = $this->log;
        $current = $this->getCurrentLesson();
        return $this->course->lessons->filter(function (Lesson $lesson) use ($log, $current) {
            return
                !$log->existsElement([
                    '__lesson_id' => $lesson->getPk(),
                ])
                &&
                $lesson->getPk() != $current->getPk();
        });
    }

    public function getLessonsProgress()
    {
        return [$this->getFinishedLessonsCount(), $this->getLessonsCount()];
    }

    public function hasLessonStepStarted(Lesson $lesson, $step)
    {
        return $this->log->existsElement([
            '__lesson_id' => $lesson->getPk(),
            'step' => $step,
            'stage' => UserLearningLog::STAGES['STARTED'],
        ]);
    }

    public function hasLessonStepFinished(Lesson $lesson, $step)
    {
        return $this->log->existsElement([
            '__lesson_id' => $lesson->getPk(),
            'step' => $step,
            'stage' => UserLearningLog::STAGES['FINISHED'],
        ]);
    }

    public function hasCurrentLessonStarted()
    {
        return $this->hasLessonStepStarted($this->getCurrentLesson(), UserLearningLog::STEPS['LESSON']);
    }

    public function startCurrentLesson()
    {
        if (!$this->hasCurrentLessonStarted()) {
            $log = new UserLearningLog();
            $log->user = $this->user;
            $log->course = $this->course;
            $log->lesson = $this->getCurrentLesson();
            $log->step = UserLearningLog::STEPS['LESSON'];
            $log->stage = UserLearningLog::STAGES['STARTED'];
            $log->eventdate = date('Y-m-d H:i:s');
            $log->save();
            $this->log = UserLearningLog::findAllByUserAndCourse($this->user, $this->course);
        }
    }

    public function hasCurrentLessonVideoStarted()
    {
        return $this->hasLessonStepStarted($this->getCurrentLesson(), UserLearningLog::STEPS['VIDEO']);
    }

    public function hasCurrentLessonVideoFinished()
    {
        return $this->hasLessonStepFinished($this->getCurrentLesson(), UserLearningLog::STEPS['VIDEO']);
    }

    public function startCurrentLessonVideo()
    {
        if (!$this->hasCurrentLessonVideoStarted()) {
            $log = new UserLearningLog();
            $log->user = $this->user;
            $log->course = $this->course;
            $log->lesson = $this->getCurrentLesson();
            $log->step = UserLearningLog::STEPS['VIDEO'];
            $log->stage = UserLearningLog::STAGES['STARTED'];
            $log->eventdate = date('Y-m-d H:i:s');
            $log->save();
            $this->log = UserLearningLog::findAllByUserAndCourse($this->user, $this->course);
        }
    }

    public function hasCurrentLessonManualStarted()
    {
        return $this->hasLessonStepStarted($this->getCurrentLesson(), UserLearningLog::STEPS['MANUAL']);
    }

    public function hasCurrentLessonManualFinished()
    {
        return $this->hasLessonStepFinished($this->getCurrentLesson(), UserLearningLog::STEPS['MANUAL']);
    }

    public function start($lesson, $step)
    {
        if (!$this->course->lessons->existsElement([Lesson::PK => $lesson])) {
            throw new Exception('Такой урок не существует в этом курсе');
        }
        $lesson = $this->course->lessons->filter(function (Lesson $l) use ($lesson) { return $l->getPk() == $lesson; })[0];

        if ($lesson->getPk() != $this->getCurrentLesson()->getPk()) {
            throw new Exception('Невозможно завершить шаг не в текущем уроке');
        }

        if (!$lesson->hasStep($step)) {
            throw new Exception('Урок не содержит указанного шага');
        }
        if ($this->hasLessonStepStarted($lesson, $step)) {
            throw new Exception('Вы уже начали указанный шаг');
        }
        if ($this->hasLessonStepFinished($lesson, $step)) {
            throw new Exception('Вы уже закончили указанный шаг');
        }

        $log = new UserLearningLog();
        $log->user = $this->user;
        $log->course = $this->course;
        $log->lesson = $lesson;
        $log->step = $step;
        $log->stage = UserLearningLog::STAGES['STARTED'];
        $log->eventdate = date('Y-m-d H:i:s');
        $log->save();

        $this->log = UserLearningLog::findAllByUserAndCourse($this->user, $this->course);
    }

    public function finish($lesson, $step)
    {
        if (!$this->course->lessons->existsElement([Lesson::PK => $lesson])) {
            throw new Exception('Такой урок не существует в этом курсе');
        }
        $lesson = $this->course->lessons->filter(function (Lesson $l) use ($lesson) { return $l->getPk() == $lesson; })[0];

        if ($lesson->getPk() != $this->getCurrentLesson()->getPk()) {
            throw new Exception('Невозможно завершить шаг не в текущем уроке');
        }

        if (!$lesson->hasStep($step)) {
            throw new Exception('Урок не содержит указанного шага');
        }
        if (!$this->hasLessonStepStarted($lesson, $step)) {
            throw new Exception('Вы еще не начали указанный шаг');
        }
        if ($this->hasLessonStepFinished($lesson, $step)) {
            throw new Exception('Вы уже закончили указанный шаг');
        }

        $log = new UserLearningLog();
        $log->user = $this->user;
        $log->course = $this->course;
        $log->lesson = $lesson;
        $log->step = $step;
        $log->stage = UserLearningLog::STAGES['FINISHED'];
        $log->eventdate = date('Y-m-d H:i:s');
        $log->save();

        $this->log = UserLearningLog::findAllByUserAndCourse($this->user, $this->course);

        return $lesson->getNextStep($step);
    }

}