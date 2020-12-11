<?php

namespace Coyote\Services\Job;

use Coyote\Feature;
use Coyote\Job;
use Coyote\Payment;
use Coyote\Repositories\Contracts\FirmRepositoryInterface as FirmRepository;
use Coyote\Repositories\Contracts\JobRepositoryInterface as JobRepository;
use Coyote\Repositories\Contracts\PlanRepositoryInterface as PlanRepository;
use Coyote\Services\Stream\Activities\Create as Stream_Create;
use Coyote\Services\Stream\Activities\Update as Stream_Update;
use Coyote\Services\Stream\Objects\Job as Stream_Job;
use Coyote\Tag;
use Coyote\User;
use Illuminate\Http\Request;

trait SubmitsJob
{
    /**
     * @var JobRepository
     */
    private $job;

    /**
     * @var FirmRepository
     */
    private $firm;

    /**
     * @var PlanRepository
     */
    private $plan;

    /**
     * @param JobRepository $job
     * @param FirmRepository $firm
     * @param PlanRepository $plan
     */
    public function __construct(JobRepository $job, FirmRepository $firm, PlanRepository $plan)
    {
        $this->job = $job;
        $this->firm = $firm;
        $this->plan = $plan;
    }

    /**
     * @param Job $job
     * @param User $user
     * @return Job
     */
    public function loadDefaults(Job $job, User $user): Job
    {
        $firm = $this->firm->loadDefaultFirm($user->id);
        $job->firm()->associate($firm);

        $job->firm->load(['benefits', 'assets']);

        $job->plan_id = request('default_plan', $this->plan->findDefault()->id);
        $job->email = $user->email;
        $job->user_id = $user->id;
        $job->setRelation('features', $this->getDefaultFeatures($job, $user));

        return $job;
    }

    /**
     * @param Job $job
     * @param User $user
     * @return Job
     */
    protected function saveRelations(Job $job, User $user)
    {
        $activity = $job->id ? Stream_Update::class : Stream_Create::class;

        if ($job->firm) {
            // fist, we need to save firm because firm might not exist.
            $job->firm->save();

            // reassociate job with firm. user could change firm, that's why we have to do it again.
            $job->firm()->associate($job->firm);
            // remove old benefits and save new ones.
            $job->firm->benefits()->push($job->firm->benefits);
            $job->firm->assets()->sync($this->request->input('firm.assets'));
        }

        $job->creating(function (Job $model) use ($user) {
            $model->user_id = $user->id;
        });

        $job->save();
        $job->locations()->push($job->locations);

        $job->tags()->sync($this->tags($this->request));
        $job->features()->sync($this->features($this->request));

        stream($activity, (new Stream_Job)->map($job));

        return $job;
    }

    protected function getDefaultFeatures(Job $job, User $user)
    {
        $features = $this->job->getDefaultFeatures($user->id);
        $models = [];

        foreach ($features as $feature) {
            $checked = (int) $feature['checked'];

            $pivot = $job->features()->newPivot([
                'checked'       => $checked,
                'value'         => $checked ? ($feature['value'] ?? null) : null
            ]);

            $models[] = Feature::findOrNew($feature['id'])->setRelation('pivot', $pivot);
        }

        return $models;
    }

    /**
     * @param Job $job
     * @return Payment|null
     */
    protected function getUnpaidPayment(Job $job): ?Payment
    {
        return !$job->is_publish ? $job->getUnpaidPayment() : null;
    }

    protected function features(Request $request): array
    {
        $features = [];

        foreach ($request->input('features', []) as $feature) {
            $checked = (int) $feature['checked'];

            $features[$feature['id']] = ['checked' => $feature['checked'], 'value' => $checked ? ($feature['value'] ?? null) : null];
        }

        return $features;
    }

    protected function tags(Request $request): array
    {
        $tags = [];
        $order = 0;

        foreach ($request->input('tags', []) as $tag) {
            $model = Tag::firstOrCreate(['name' => $tag['name']]);

            $tags[$model->id] = [
                'priority'  => $tag['priority'] ?? 0,
                'order'     => ++$order
            ];
        }

        return $tags;
    }
}
