<?php

namespace Coyote\Notifications\Job;

use Coyote\Job\Comment;
use Coyote\Services\Notification\Notification;
use Coyote\Services\UrlBuilder;
use Coyote\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;

class CommentedNotification extends Notification implements ShouldQueue, ShouldBroadcastNow
{
    use Queueable;

    const ID = \Coyote\Notification::JOB_COMMENT;

    /**
     * @var Comment
     */
    private $comment;

    private string $commentUrl;

    /**
     * @param Comment $comment
     */
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
        $this->commentUrl = UrlBuilder::jobComment($this->comment->job, $this->comment->id);
    }

    /**
     * @param User $user
     * @return array
     */
    public function toDatabase($user)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $user->id,
            'type_id'       => static::ID,
            'subject'       => $this->comment->job->title,
            'excerpt'       => excerpt($this->comment->html),
            'url'           => $this->commentUrl,
            'id'            => $this->id
        ];
    }

    /**
     * Unikalne ID okreslajace dano powiadomienie. To ID posluzy do grupowania powiadomien tego samego typu
     *
     * @return string
     */
    public function objectId()
    {
        return substr(md5(class_basename($this) . $this->comment->job->id), 16);
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'name' => $this->comment->user_id ? $this->comment->user->name : $this->comment->email,
            'user_id' => $this->comment->user_id
        ];
    }

    /**
     * @return MailMessage
     */
    public function toMail()
    {
        return (new MailMessage())
            ->subject($this->getMailSubject())
            ->line(
                sprintf(
                    'Do ogłoszenia <b>%s</b> dodany został nowy komentarz.',
                    link_to(UrlBuilder::job($this->comment->job), $this->comment->job->title)
                )
            )
            ->action(
                'Kliknij, aby go zobaczyć i odpowiedzieć',
                $this->redirectionUrl()
            )
            ->line('Otrzymujesz to powiadomienie ponieważ dodałeś to ogłoszenie do ulubionych lub jesteś jego autorem.');
    }

    /**
     * @return BroadcastMessage
     */
    public function toBroadcast()
    {
        return new BroadcastMessage([
            'headline'  => $this->getMailSubject(),
            'subject'   => $this->comment->job->title,
            'url'       => $this->redirectionUrl()
        ]);
    }

    public function toWebPush(): WebPushMessage
    {
        return (new WebPushMessage())
            ->title($this->getMailSubject())
            ->icon('/img/favicon.png')
            ->tag($this->redirectionUrl())
            ->body($this->comment->job->title)
            ->data(['url' => $this->redirectionUrl()])
            ->options(['TTL' => 1000]);
    }

    /**
     * @return string
     */
    private function getMailSubject(): string
    {
        return sprintf('Nowy komentarz do ogłoszenia %s.', $this->comment->job->title);
    }

    protected function redirectionUrl(): string
    {
        return route('user.notifications.redirect', ['path' => urlencode($this->commentUrl)]);
    }
}
