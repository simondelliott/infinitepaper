<?php

class UserControler extends ApplicationControler{
	
    private function show_login(){
            $user = Session::get("user");
            $view = new LoginView($user);
            $view->show();
    }
	
    public function login (){
        if ($_POST["user"]){
            $user = User::find_by_username_and_password($_POST["user"]);

            if ($user){

                //debug("found a user");

                //a user has been found who matches the posted user details
                //store the user in the session
                Session::put("user",$user);
                //debug("put user in teh session");

                if($user->is_administrator()){
                    //debug("redirecting");
                    header("Location: /admin/index");
                    exit();
                }
                else{
                    header("Location: /");
                }
                return;
            }
            else{
                //no user found so create a temp user object to display
                $failed_user = new User($_POST["user"]);
                Session::put("user",$failed_user);

                $this->show_login();
                return;
            }
            exit();
        }
        else{
            //just normal loging in then
            //get the session here
            $this->show_login();
        }
    }

    public function logout (){
        Session::delete("user");
        header("Location: /");
        return;
    }
	
}

?>
