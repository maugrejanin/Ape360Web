<?php 

class SessionManager implements SessionHandlerInterface{

	private $table_session_name = 't_session';

	public function __construct(){

		session_set_save_handler(
			[$this, 'open'],
			[$this, 'close'],
			[$this, 'read'],
			[$this, 'write'],
			[$this, 'destroy'],
			[$this, 'gc']
		);

		register_shutdown_function('session_write_close');
    	
		if(session_status() == PHP_SESSION_NONE)
			session_start();

	}

	public function open($savepath, $id){
        //You can implement a specific database to manage sessions.
        return true;

        //return false;
    }

    public function read($id){
        $research = Model::search("SELECT `data` FROM $this->table_session_name WHERE id_session = ? LIMIT 1", [$id], false);

        if ($research)
            return $research['data'];
        else
            return '';
    }

    public function write($id, $data){
        $access = time();

        $data_replace[0] = $id;
        $data_replace[1] = $access;
        $data_replace[2] = $data;

        if (Model::exec("REPLACE INTO $this->table_session_name (id_session, access, `data`) VALUES (?, ?, ?)", $data_replace))
            return true;
        else
            return false;
    }

    public function destroy($id){
		if (Model::exec("DELETE FROM $this->table_session_name WHERE id_session = ? LIMIT 1", [$id]))
            return true;
        else
            return false;
    }

    public function close(){
        //If you implement a specific database to manage sessions, so close it.

        // global $mypdo;
        // $mypdo = null;

        return true;
    }

    public function gc($max){//clean
    	$old = time() - $max;

        if (Model::exec("DELETE FROM $this->table_session_name WHERE access < ?", [$old]))
            return true;
        else
            return false;
    }

    public function __destruct(){
        $this->close();
    }

}