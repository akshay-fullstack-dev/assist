<?php

namespace App\Console\Commands;

use App\Traits\SandgridTrait;
use App\User;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use stdClass;

class SandgridSync extends Command
{
    use SandgridTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sandgrid:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to sync the user to the sandgrid';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users = User::where('sandgrid_status', User::notSyncWithSandgrid)->with('selectedAddress')->get();
        if ($users->count()) {
            $sync_users = array();
            foreach ($users as $user) {
                if ($user->hasRole(['user'])) {
                    $sandgrid_list_category_token = config('sandgrid.sandgrid_vendor_list_id');
                } else {
                    // vendor and agency
                    $sandgrid_list_category_token = config('sandgrid.sandgrid_user_list_id');
                }
                $user_data = $this->createUserData($user);

                $isSyced = $this->add_user_to_sandgrid($user_data, $sandgrid_list_category_token);

                if ($isSyced) {
                    array_push($sync_users, $user->id);
                }
            }

            // update the user it they are synced
            if (count($sync_users) > 0) {
                DB::table('users')
                    ->whereIn('id', $sync_users)
                    ->update(['sandgrid_status' => '1']);
            }
        }
    }

    // create data for the sandgrid
    private function createUserData($user)
    {
        $user_address = $user->selectedAddress;
        return (object) array(
            "address_line_1" => $user_address->full_address ?? "",
            "address_line_2" => "",
            "alternate_emails" => array(),
            "city" => $user_address->city ?? "",
            "country" => $user_address->country ?? "",
            // email is required
            "email" => $user->email,
            "user_name" => $user->firstname . " " . $user->lastname,
            "postal_code" => $user->pincode ?? "",
        );
    }
}
