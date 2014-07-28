sudo apt-get install cgroup-lite rc curl p7zip 

/root/profondui/system/sched.sh install root

curl -X POST --data 'idmachine=%profond_idmachine%&key=%profond_keymachine%' http://%profond_url%/isready