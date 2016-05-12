<?php

namespace Coyote\Http\Controllers\User;

use Coyote\Repositories\Contracts\AlertRepositoryInterface as Alert;
use Coyote\Repositories\Contracts\SessionRepositoryInterface as Session;
use Coyote\Repositories\Contracts\UserRepositoryInterface as User;
use Illuminate\Http\Request;
use Carbon;

class AlertsController extends BaseController
{
    use SettingsTrait, HomeTrait {
        SettingsTrait::getSideMenu as settingsSideMenu;
        HomeTrait::getSideMenu as homeSideMenu;
    }

    /**
     * @var User
     */
    private $user;

    /**
     * @var Alert
     */
    private $alert;

    /**
     * @param User $user
     * @param Alert $alert
     */
    public function __construct(User $user, Alert $alert)
    {
        parent::__construct();

        $this->user = $user;
        $this->alert = $alert;
    }

    /**
     * @return mixed
     */
    public function getSideMenu()
    {
        if ($this->getRouter()->currentRouteName() == 'user.alerts') {
            return $this->homeSideMenu();
        } else {
            return $this->settingsSideMenu();
        }
    }

    /**
     * @param Session $session
     * @return $this
     */
    public function index(Session $session)
    {
        $this->breadcrumb->push('Powiadomienia', route('user.alerts'));

        $alerts = $this->alert->paginate($this->userId);
        $session = $session->findBy('user_id', $this->userId, ['created_at']);

        // mark as read
        $this->mark($alerts);

        return $this->view('user.alerts.home')->with(compact('alerts', 'session'));
    }

    /**
     * Mark alerts as read and returns number of marked alerts
     *
     * @param $alerts
     * @return int
     */
    private function mark($alerts)
    {
        $markId = [];
        foreach ($alerts as $alert) {
            if (!$alert->read_at) {
                $markId[] = $alert->id;
            }
        }

        if (!empty($markId)) {
            $this->alert->markAsRead($markId);
        }

        return count($markId);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function settings()
    {
        $this->breadcrumb->push('Ustawienia powiadomień', route('user.alerts.settings'));
        $settings = $this->alert->getUserSettings($this->userId);

        return $this->view('user.alerts.settings', compact('settings'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Request $request)
    {
        $this->alert->setUserSettings($this->userId, $request->input('settings'));

        return back()->with('success', 'Zmiany zostały zapisane');
    }

    /**
     * @param Session $session
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax(Session $session, Request $request)
    {
        $unread = auth()->user()->alerts_unread;

        $alerts = $this->alert->takeForUser($this->userId, max(10, $unread), $request->query('offset', 0));
        $unread -= $this->mark($alerts);

        $view = view('user.alerts.ajax', [
            'alerts'    => $alerts,
            'session'   => $session->findBy('user_id', $this->userId, ['created_at']),
        ])->render();

        return response()->json([
            'html'      => $view,
            'unread'    => $unread
        ]);
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        $this->alert->delete($id);
    }

    /**
     * Marks one or more alerts as read
     *
     * @param null|int $id
     */
    public function markAsRead($id = null)
    {
        if ($id) {
            $this->alert->update(['is_marked' => true], $id);
        } else {
            if (auth()->user()->alerts_unread) {
                $this->alert->where('user_id', $this->userId)->where('read_at', 'IS', null)->update([
                    'read_at' => Carbon::now()
                ]);
            }

            $this->alert->where('user_id', $this->userId)->update(['is_marked' => true]);
        }
    }
}
