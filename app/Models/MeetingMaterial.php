<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeetingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_meeting_schedule',
        'id_sni_lama',
        'status_sni_lama',
        'status_nodin',
        'catatan',
    ];

    public function transition_times()
    {
        return $this->hasOne(TransitionTime::class, 'id_sni_lama');
    }

    public function meeting_schedules()
    {
        return $this->belongsTo(MeetingSchedule::class, 'id_meeting_schedule');
    }

    public function old_standards()
    {
        return $this->belongsTo(OldStandard::class, 'id_sni_lama');
    }
}
