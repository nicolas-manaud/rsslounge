<?PHP

/**
 * Model for accessing the users
 *
 * @package    application_models
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class application_models_users extends application_models_base {
   
    
    /**
     * set up the table name
     *
     * @return void
     */
    protected function _setupTableName() {
        $this->_name = Zend_Registry::get('config')->resources->db->prefix . 'users';
        parent::_setupTableName();
    }
    
    
    /**
     * set up metadata as other reference table objects
     * and dependend table objects
     *
     * @return void
     */
    protected function _setupMetadata() {
        $this->_dependentTables = array(
            );
        parent::_setupMetadata();
    }
    
    /**
     * authenticate
     *
     * @return void
     * @param string $username
     * @param string $password
     */
    public function authenticate($username, $password) {
        $res = $this->fetchAll(
                    $this->select()->where('username=?', $username)->where('password=?', sha1($password))
                );
        if($res->count()==1)
            return true;
        return false;
    }
    
    /**
     * returns the username
     *
     * @return bool|string username or false
     */
    public function getUsername() {
        $res = $this->fetchAll();
        if($res->count()==0)
            return false;
        else
            return $res->current()->username;
    }
    
    /**
     * delete user
     *
     * @return void
     */
    public function purge() {
        $this->delete('');
    }
    
    /**
     * set username and password
     *
     * @return void
     * @param string $username
     * @param string $password
     */
    public function setUser($username, $password) {
        $res = $this->fetchAll();
        if($res->count()==1) {
            $res->current()->username = $username;
            $res->current()->password = sha1($password);
            $res->current()->save();
        } else {
            $this->insert(array(
                'username' => $username,
                'password' => sha1($password)
            ));
        }
    }
    
}


?>