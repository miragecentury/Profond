<?php

namespace Profondlib\Lanceur\Adapter;

use Profond\Entity\Machine;
use Profond\Entity\Project;
use Profond\Entity\User;
use Profondlib\Lanceur\Adapter\AbstractAdapter;

class SshAdapter {

    const BASE_PATH_STORAGE = "./data/storage/system/";
    const BASE_PATH_CACHE = "./data/cache/";
    const INSTALL_FILE = "install.sh";
    const SCHED_FILE = "sched.sh";
    const PROFOND_URL = "profond.local";

    /**
     *
     * @var Machine
     */
    protected $Machine;
    protected $config;
    protected $ssh;
    protected $sftp;
    protected $isAuth = false;

    public function __construct(Machine $Machine) {
        $this->config = $Machine->getConfig();
        $this->Machine = $Machine;
    }

    /**
     * @return Boolean Description
     */
    public function connect() {
        $ip = $this->config["ip"];
        $username = $this->config["username"];
        $password = $this->config["password"];

        if (($res = ssh2_connect($ip)) !== false) {
            if (is_resource($res)) {
                if (ssh2_auth_password($res, $username, $password)) {
                    $this->ssh = $res;
                    $this->sftp = ssh2_sftp($this->ssh);
                    $this->isAuth = true;
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getNbCPU() {
        if (($stream = $this->exec("cat /proc/cpuinfo | grep -c processor")) !== FALSE) {
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);
            stream_set_blocking($errorStream, true);
            stream_set_blocking($stream, true);
            return trim(stream_get_contents($stream));
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $exec
     */
    public function exec($exec) {
        if ($this->isAuth) {
            return ssh2_exec($this->ssh, $exec);
        } else {
            return false;
        }
    }

    public function installPROFONDUI() {
        $this->createDir("profondui");
        $this->createDir("profondui/jobs");
        $this->createDir("profondui/system");
        $this->send("profondui/system/sched.sh", self::BASE_PATH_STORAGE . self::SCHED_FILE);
        $cache_install = self::BASE_PATH_CACHE . "install_" . $this->Machine->getId() . ".sh";
        copy(self::BASE_PATH_STORAGE . self::INSTALL_FILE, $cache_install);
        $this->setupInstallscript($this->Machine, $cache_install);
        $this->send("profondui/system/install.sh", $cache_install);
        $this->exec("chmod +x profondui/system/sched.sh");
        $this->exec("chmod +x profondui/system/install.sh");
        $this->exec("apt-get install nohup");
        $this->exec("nohup ~/profondui/system/install.sh &");
    }

    private function setupInstallscript(Machine $Machine, $pathToInstallfile) {
        $file_str = file_get_contents($pathToInstallfile);
        $file_str = str_replace("%profond_idmachine%", $Machine->getId(), $file_str);
        $file_str = str_replace("%profond_keymachine%", $Machine->getKeypass(), $file_str);
        $file_str = str_replace("%profond_url%", self::PROFOND_URL, $file_str);
        file_put_contents($pathToInstallfile, $file_str);
    }

    public function send($dist, $local) {
        return ssh2_scp_send($this->ssh, $local, $dist);
    }

    public function recept($dist, $local) {
        return ssh2_scp_recv($this->ssh, $dist, $local);
    }

    public function createDir($path) {
        return ssh2_sftp_mkdir($this->sftp, $path, 0775, true);
    }

    /**
     * 
     */
    public function disconnect() {
        ssh2_exec($this->ssh, "exit");
    }

    /**
     * 
     * @param \Profond\Entity\Machine $Machine
     * @return Boolean Description
     */
    public static function checkCompatibility(Machine $Machine) {
        
    }

    /**
     * 
     * @param type $arrayConfig
     * @return Boolean Description
     */
    public static function checkConnection($arrayConfig) {
        $ip = $arrayConfig["ip"];
        $username = $arrayConfig["username"];
        $password = $arrayConfig["password"];

        if (($res = ssh2_connect($ip)) !== false) {
            if (is_resource($res)) {
                if (ssh2_auth_password($res, $username, $password)) {
                    ssh2_exec($res, "exit");
                    return true;
                } else {
                    ssh2_exec($res, "exit");
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $ip
     * @param type $username
     * @param type $password
     * @return Array Description
     */
    public static function formatConfig($ip, $username, $password) {
        return array(
            "ip" => $ip,
            "username" => $username,
            "password" => $password
        );
    }

    /**
     * 
     * @param type $ip
     * @param type $username
     * @param type $password
     * @return string
     */
    public static function formatConfigToJson($ip, $username, $password) {
        return json_encode(
                array(
                    "ip" => $ip,
                    "username" => $username,
                    "password" => $password
                )
        );
    }

    /**
     * 
     * @param string $json
     * @return Array
     */
    public static function formatJsonToConfig($json) {
        return json_decode($json);
    }

}
