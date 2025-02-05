<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobBatch extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'job_batches';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'options'      => 'collection',
        'failed_jobs'  => 'integer',
        'created_at'   => 'datetime',
        'cancelled_at' => 'datetime',
        'finished_at'  => 'datetime',
    ];

    protected $guarded = [];

    public function campaign()
    {
        return $this->belongsTo(LeadDistributionCampaign::class, 'campaign_id');
    }

        /**
     * Get the total number of jobs that have been processed by the batch thus far.
     *
     * @return int
     */
    public function processedJobs()
    {
        return $this->total_jobs - $this->pending_jobs;
    }

        /**
     * Get the percentage of jobs that have been processed (between 0-100).
     *
     * @return int
     */
    public function progress()
    {
        return $this->total_jobs > 0 ? round(($this->processedJobs() / $this->total_jobs) * 100) : 0;
    }

}