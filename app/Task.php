<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cron\CronExpression;
use Illuminate\Support\Facades\Cache;


class Task extends Model
{
    protected $fillable = [
        'description',
        'command',
        'expression',
        'dont_overlap',
        'run_in_maintenance',
        'notification_email'
    ];

    // last_run

    public function getLastRunAttribute()
    {
        if($last = $this->results()->orderBy('id','desc')->first()){
            return $last->ran_at->format("Y-m-d h:i A");
        }
        return 'N/A';
    }

    public function getAverageRuntimeAttribute(){
        return number_format($this->results()->avg('duration') / 1000,2);
    }

    public function getNextRunAttribute()
    {
        return CronExpression::factory($this->getCronExpression())->getNextRunDate('now',0,false,'Asia/Karachi')->format('Y-m-d h:i A');
    }

    public function getCronExpression()
    {
        return $this->expression ?: '* * * * *';
    }

    public function results(){
        return $this->hasMany(Result::class);
    }

    public function getActive()
    {
        return Cache::rememberForever('tasks.active',function(){
            return $this->getAll()->filter(function($task){
                return $task->is_active;
            });
        });
    }

    public function getAll(){
        return Cache::rememberForever('tasks.all',function(){
            return $this->all();
        });
    }
}
