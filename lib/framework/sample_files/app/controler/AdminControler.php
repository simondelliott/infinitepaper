<?php

class AdminControler extends ApplicationControler{
	
    public function index(){
        //if the application has been installed then make sure that the user is logged in
        User::ensure_logged_in();
        $user = User::get_current_user();

        if(!$user->is_administrator()){
                header("/");
                return;
        }
        $view = new AdminView($user);
        $view->show();
    }

    public function kill_all_sessions(){
        User::ensure_logged_in();
        $user = User::get_current_user();
        if(!$user->is_administrator()){
                header("Location: /");
                return;
        }

        Session::kill_all_sessions();

        header("Location: /admin/index");
        return;
    }
}

?>
