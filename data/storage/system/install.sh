apt-get install curl

~/profondui/system/sched.sh install root

curl -X POST --data 'idmachine=%profond_idmachine%&key=%profond_keymachine%' http://%profond_url%/isready