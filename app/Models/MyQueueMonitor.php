<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use \Croustibat\FilamentJobsMonitor\Models\QueueMonitor as CroustibatQueueMonitor;

class MyQueueMonitor extends CroustibatQueueMonitor {}
