#!/bin/bash
#
# sched.sh v0.8 02/05/2013
#
# 19/01/2013 : Suport de l'affectation des CPUS
# 01/05/2013 : Correction de bugs dans stat
# 02/05/2013 : Ajout de l'ordre cpuset pour modifier l'affectation des cpu
# 02/05/2013 : Ajout des commandes help, history et migre
#
# Paquets necessaire :
# Ubuntu : cgroup-lite
# RedHat/CentOS : libcgroup-tools
# Debian : libcgroup1

cd $(dirname $0)
CHEMIN=$(pwd)/$(basename $0)
# Le nom du programme
MYCGNAME="sched2"
# Le nom du nouveau controlleur
MYCONTNAME="profond"
# Le repertoire ou on sauvegarde des parametres
MYVARNAME="/var/profond"
# Le repertoire habituel ou sont montés les cgroups
CGROUPDIR="/sys/fs/cgroup"
# Besoin des 4 controleurs : "cpuacct cpu freezer et cpuset"
# Mais ss Fedora cpu et cpuacct pointent au meme endroit
CONTROLNEED="cpuacct cpu freezer cpuset"
CPUACCT="cpuacct"
CPUSET="cpuset"
FREEZER="freezer"
CPU="cpu"
typeset -i NBCPU=$(cat /proc/cpuinfo|grep processor|wc -l)

MYUID=$(id -u)

fct_erreur()
{
  echo "Erreur:$*"
  exit 1
}

fct_help()
{
  echo "---------------------------------------------------"
  echo "  Scheduleur $0 pour CGROUP - C. Drocourt"
  echo "---------------------------------------------------"
  echo ""
  echo "Syntaxe : $0 <ordre> [options] [parametres]"
  echo ""
  echo "Ordres :"
  echo "  install <group> : pour installer le scheduleur utilisable par le groupe <group>"
  echo "  uninstall       : l'inverse"
  echo "  list            : liste les jobs sous la forme"
  echo "                    <num job>;<etat>;<ligne de commande>"
  echo "  history         : liste les jobs terminés sous la forme"
  echo "                    <date_et_heure_de_fin>;<num job>;tps exec en sec;conso cpu en ms;<ligne de commande>"
  echo "  stat            : liste les jobs sous la forme :"
  echo "                    <num job>;<etat>;<PRIORITE>;temp exec en s;conso cpu en ms;CPUSETS;Reserve;Reserve;Reserve;<ligne de commande>"
  echo "  info   <XX>     : donne des infos sur le job <XX>, une ligne par processus dans le job de la forme"
  echo "                    <job>;<commande>;<pid>;<liste threads separe par des virgules>"
  echo "  pause  <XX>     : Met en pause le job, et donc ttes les taches (processus/threads) qu'il contient,"
  echo "  resume <XX>     : Reprend l'execution du job"
  echo "  stop   <XX>     : Stop le job XX et toutes ses taches"
  echo "  create <-c cpuset> <-d \"rep\"> <-m \"mail\"> <-u \"url\"> <commande avec arguments>"
  echo "                    lance la commande et affiche OK:000:num job"
  echo "  migre  <-c cpuset> <-d \"rep\"> <-m \"mail\"> <-u \"url\"> <PID>"
  echo "                    migre le PID et affiche OK:000:num job"
  echo ""
  echo "En cas d'erreur une commande affiche Erreur:<code>:<Message>"
  echo ""
}

fct_stop()
{
  SSPARA=$1
  typeset -i DATEFIN=$(date +%s)
  typeset -i DATEDEB=$(cat $MYVARNAME/$MYCGNAME/$SSPARA/timestamp)
  typeset -i TEMPEXEC=$DATEFIN-$DATEDEB
  COMMANDE=$(cat $MYVARNAME/$MYCGNAME/$SSPARA/commande)
  DATEACTU=$(date +%d/%m/%Y-%H:%M:%S)
  typeset -i CPUACCT=$(cat $CGROUPDIR/$CPUACCT/$MYCGNAME/$SSPARA/cpuacct.usage)
  let CPUACCTMS=$CPUACCT/1000000
  echo "$DATEACTU;$SSPARA;$TEMPEXEC;$CPUACCTMS;$COMMANDE" >> $MYVARNAME/$MYCGNAME/history.txt
  MONURL=$(cat $MYVARNAME/$MYCGNAME/$SSPARA/url)
  MONMAIL=$(cat $MYVARNAME/$MYCGNAME/$SSPARA/mail)
  if [ "$MONURL." != "." ]; then
    NEWURL=$(echo $MONURL | sed "s/%j/$SSPARA/g")
    echo $NEWURL >> /tmp/sched23.txt
    curl --request POST $NEWURL --data "job=$SSPARA&tempexec=$TEMPEXEC&cpuacct=$CPUACCTMS"
  fi
  if [ "$MONMAIL." != "." ]; then
    MESSAGE="Fin de job\nJob: $SSPARA\nTemps execution(s): $TEMPEXEC\nCPU Acct (ms): $CPUACCTMS\nCommande: $COMMANDE"
    echo -e $MESSAGE|mail -s "Profond Scheduler : Fin de Job" $MONMAIL
  fi
  for i in $CONTROLNEED
  do
    if [ -d $CGROUPDIR/$i/$MYCGNAME/$SSPARA ]; then
      rmdir $CGROUPDIR/$i/$MYCGNAME/$SSPARA
    fi
  done
  rm -r $MYVARNAME/$MYCGNAME/$SSPARA
}

if [ $# -lt 1 ]
then
  fct_erreur 100:Syntaxe "$0 <ordre> <commande> <arg0> <arg1> ..."
fi

if [ ! -d $CGROUPDIR ]
then
  fct_erreur 200:"Le repertoire racine des CGROUP est inexistant ($CGROUPDIR)"
fi

CONTROLLIST=""
for i in $CONTROLNEED
do
  if [ "$CONTROLLIST." == "." ]
  then
    CONTROLLIST="$i"
  else
    CONTROLLIST="$CONTROLLIST,$i"
  fi
done

if [ "$1" != "install" ]; then
  if [ ! -d $CGROUPDIR/$MYCONTNAME ]; then
    fct_erreur 200:"Le Scheduleur $MYCONTNAME est inexistant. Peut etre refaire $0 install"
  fi
fi

for i in $CONTROLNEED
do
  PASBESOIN=$(mount | grep "$CGROUPDIR" | grep "[[:punct:]]$i[[:punct:]]")
  if [ "$?." != "0." ]
  then
    fct_erreur 200:"Le controlleur CGROUP $i est inexistant. Il faut installer le paquet libcgroup-tools(RedHat), libcgroup1 (Debian) ou cgroup-lite (ubuntu) puis EVENTUELLEMENT configurer le fichier de configuration /etc/cgconfig.conf pour monter les cgroups dans le répertoire $CGROUPDIR"
  fi
done

CONTROLNEED="$MYCONTNAME $CONTROLNEED"

SSPARA1=$(echo $1|awk -F'/' '{ print $2 }')
# Le script est appele directement par le kernel
if [ "$SSPARA1." = "$MYCGNAME." ]; then
  SSPARA2=$(echo $1|awk -F'/' '{ print $3 }')
  if [ "$SSPARA2." != "." ];then
    fct_stop $SSPARA2
  fi
  exit 0
fi

case "$1" in
  help)
    fct_help
    exit 0
    ;;
  history)
	  if [ -f "$MYVARNAME/$MYCGNAME/history.txt" ];then
      cat "$MYVARNAME/$MYCGNAME/history.txt"
    fi
		exit 0
    ;;
  install)
    if [ "$MYUID" != "0" ]; then
      fct_erreur 100:"Il faut être root ..."
    fi
    shift
    if [ "$1." = "." ]; then
      fct_erreur 100:Syntaxe "$0 install <group>"
    fi
    mkdir $CGROUPDIR/$MYCONTNAME
    mount -t cgroup -o none,name=$MYCONTNAME none $CGROUPDIR/$MYCONTNAME
    for i in $CONTROLNEED
    do
      if [ ! -d $CGROUPDIR/$i/$MYCGNAME ]; then
        mkdir $CGROUPDIR/$i/$MYCGNAME
        chown root.$1 $CGROUPDIR/$i/$MYCGNAME
        chmod 775 $CGROUPDIR/$i/$MYCGNAME
        chown root.$1 $CGROUPDIR/$i/$MYCGNAME/tasks
        chmod 775 $CGROUPDIR/$i/$MYCGNAME/tasks
      fi
      if [ "$i." = "cpuset." ]; then
        let MAXCPU=NBCPU-1
        echo "0-$MAXCPU" > $CGROUPDIR/$CPUSET/$MYCGNAME/cpuset.cpus
        echo "0" > $CGROUPDIR/$CPUSET/$MYCGNAME/cpuset.mems
      fi
    done
    echo "$CHEMIN" > $CGROUPDIR/$MYCONTNAME/release_agent
    echo "1" > $CGROUPDIR/$MYCONTNAME/$MYCGNAME/notify_on_release
    if [ ! -d $MYVARNAME ]; then mkdir $MYVARNAME;fi
    if [ ! -d $MYVARNAME/$MYCGNAME ]; then mkdir $MYVARNAME/$MYCGNAME;fi
    chown root.$1 $MYVARNAME/$MYCGNAME
    chmod 775 $MYVARNAME/$MYCGNAME
    if [ -f /etc/rc.local ]; then
      grep "$0" /etc/rc.local
      if [ "$?." != "0." ]; then
        # Pour Debian
        grep "^exit 0" /etc/rc.local > /dev/null
        if [ "$?." = "0." ]; then
          grep -v "^exit 0" /etc/rc.local > /tmp/rc.local.$$
          echo "$CHEMIN install $1" >> /tmp/rc.local.$$
          echo "exit 0" >> /tmp/rc.local.$$
          cp -af /etc/rc.local /etc/rc.local.$$
          cp -af /tmp/rc.local.$$ /etc/rc.local
          rm /tmp/rc.local.$$
        else
          echo "$CHEMIN install $1" >> /etc/rc.local
        fi
      fi
    fi
    exit 0
    ;;
  uninstall)
    if [ "$MYUID" != "0" ]; then
      fct_erreur 100:"Il faut être root ..."
    fi
    for i in $CONTROLNEED
    do
      if [ -d $CGROUPDIR/$i/$MYCGNAME ]; then
        rmdir $CGROUPDIR/$i/$MYCGNAME
      fi
    done
    umount $CGROUPDIR/$MYCONTNAME
    rmdir $CGROUPDIR/$MYCONTNAME
    rm -r $MYVARNAME
    if [ -f /etc/rc.local ]; then
      MONSCRIPT=$(basename $0)
      grep "$MONSCRIPT" /etc/rc.local
      if [ "$?." = "0." ]; then
         cp -af /etc/rc.local /tmp/rc.local.$$
        grep -v "$0" /etc/rc.local > /tmp/rc.local.$$
        cp -af /etc/rc.local /etc/rc.local.$$
        cp -af /tmp/rc.local.$$ /etc/rc.local
        rm /tmp/rc.local.$$
      fi
    fi
    exit 0
    ;;
 list)
    shift
    for i in $(ls $CGROUPDIR/$MYCONTNAME/$MYCGNAME)
    do
      if [ -d $CGROUPDIR/$MYCONTNAME/$MYCGNAME/$i ]; then
        ETAT=$(cat $CGROUPDIR/$FREEZER/$MYCGNAME/$i/freezer.state)
        if [ "$ETAT." = "FROZEN." ];then 
          MYETAT="pause"
        else
          MYETAT="play"
        fi
        COMMANDE=$(cat $MYVARNAME/$MYCGNAME/$i/commande)
        echo "$i;$MYETAT;$COMMANDE"
      fi
    done
    exit 0
    ;;
 stat)
    typeset -i DATEDEB
    typeset -i DATEFIN
    typeset -i TEMPEXEC
    typeset -i CPUACCT
    shift
    for i in $(ls $CGROUPDIR/$MYCONTNAME/$MYCGNAME)
    do
      if [ -d $CGROUPDIR/$MYCONTNAME/$MYCGNAME/$i ]; then
        let DATEDEB=$(cat $MYVARNAME/$MYCGNAME/$i/timestamp)
        let DATEFIN=$(date +%s)
        let TEMPEXEC=$DATEFIN-$DATEDEB
        let CPUACCTVAL=$(cat $CGROUPDIR/$CPUACCT/$MYCGNAME/$i/cpuacct.usage)
        let CPUACCTMS=$CPUACCTVAL/1000000
        COMMANDE=$(cat $MYVARNAME/$MYCGNAME/$i/commande)
        ETAT=$(cat $CGROUPDIR/$FREEZER/$MYCGNAME/$i/freezer.state)
        PRIORITE=$(cat $CGROUPDIR/$CPU/$MYCGNAME/$i/cpu.shares)
        MYCPUSET=$(cat $CGROUPDIR/$CPUSET/$MYCGNAME/$i/cpuset.cpus)
        if [ "$ETAT." = "FROZEN." ];then 
          MYETAT="pause"
        else
          MYETAT="play"
        fi
        if [ "$MYCPUSET." = "." ]; then
          MYCPUSET="All"
        fi
        echo "$i;$MYETAT;$PRIORITE;$TEMPEXEC;$CPUACCTMS;$MYCPUSET;;;;$COMMANDE"
      fi
    done
    exit 0
    ;;
  info)
    shift
    if [ $# -lt 1 ]
    then
      fct_erreur 100:"$0 info <num job>"
    fi
    for i in $(cat $CGROUPDIR/$MYCONTNAME/$MYCGNAME/$1/tasks)
    do
      TGID=$(grep "Tgid" /proc/$i/status|awk '{ print $2 }')
      if [ "$i." = "$TGID." ]; then
        NUMTHREADS=""
        for j in $(ls -1 /proc/$i/task/)
        do
          if [ "$j." != "$i." ];then
            if [ "$NUMTHREADS." = "." ];then
              NUMTHREADS=$j
            else
              NUMTHREADS=$NUMTHREADS,$j
            fi
          fi
        done
        LACOMMANDE=$(cat /proc/$i/cmdline| tr "\0" " ")
        echo "$1;$LACOMMANDE;$i;$NUMTHREADS"
      fi
    done
    exit 0
    ;;
  pause)
    shift
    echo FROZEN > $CGROUPDIR/$FREEZER/$MYCGNAME/$1/freezer.state
    exit 0
    ;;
  resume)
    shift
    echo THAWED > $CGROUPDIR/$FREEZER/$MYCGNAME/$1/freezer.state
    exit 0
    ;;
  stop)
    shift
    if [ $# -lt 1 ]
    then
      fct_erreur 100:"$0 stop <num job>"
    fi
    if [ ! -e $CGROUPDIR/$MYCONTNAME/$MYCGNAME/$1/tasks ];then
      fct_erreur 100:"job $1 inexistant"
    fi
    for i in $(cat $CGROUPDIR/$MYCONTNAME/$MYCGNAME/$1/tasks)
    do
      kill -9 $i
    done
    exit 0
    ;;
  cpuset)
    shift
    if [ $# -lt 2 ]
    then
      fct_erreur 100:"$0 cpuset <num job> <cpuset>"
    fi
    if [ ! -e $CGROUPDIR/$MYCONTNAME/$MYCGNAME/$1/tasks ];then
      fct_erreur 100:"job $1 inexistant"
    fi
    if [ ! -d $CGROUPDIR/$CPUSET/$MYCGNAME/$1/ ];then
      fct_erreur 100:"CPUSET non implemente"
    fi
		CPUSETVAL=$2
    let MAXCPU=NBCPU-1
		CPUSETVALMAX=$(echo $CPUSETVAL|awk -F'-' '{ print $2 }')
		if [ "$CPUSETVALMAX." != "." ]; then
      let CPUSETVALMAXINT=$CPUSETVALMAX
		  if [ $CPUSETVALMAXINT -gt $MAXCPU ]; then
        fct_erreur 100:"CPUSET trop grand pour $NBCPU cpus possibles"
			fi
		fi
    echo "$CPUSETVAL" > $CGROUPDIR/$CPUSET/$MYCGNAME/$1/cpuset.cpus
    echo "0" > $CGROUPDIR/$CPUSET/$MYCGNAME/$1/cpuset.mems
    echo "$$" >> $CGROUPDIR/$CPUSET/$MYCGNAME/$1/tasks
		exit 0
    ;;
  create)
    shift
    if [ $# -lt 1 ]
    then
      fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
    fi
    AUTREARGU=1
    ADDRMAIL=""
    REPERT=$PWD
    MYURL=""
    let MAXCPU=NBCPU-1
    CPUSETVAL="0-$MAXCPU"
    while [ "$AUTREARGU." = "1." ]
    do
      AUTREARGU=0
      case "$1" in
       "-d")
          if [ $# -lt 3 ]
          then
            fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
          fi
          shift
          REPERT=$1
          cd $REPERT
          shift
          AUTREARGU=1
          ;;
       "-m")
          if [ $# -lt 3 ]
          then
            fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
          fi
          shift
          ADDRMAIL=$1
          shift
          AUTREARGU=1
          ;;
       "-u")
          if [ $# -lt 3 ]
          then
            fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
          fi
          shift
          MYURL=$1
          shift
          AUTREARGU=1
          ;;
       "-c")
          if [ $# -lt 3 ]
          then
            fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
          fi
          shift
          CPUSETVAL=$1
          shift
          AUTREARGU=1
          ;;
      esac
    done

    # Je cree un nouveau cgroup pour le job
    for i in $CONTROLNEED
    do
      if [ ! -d $CGROUPDIR/$i/$MYCGNAME/$$ ]; then
        mkdir $CGROUPDIR/$i/$MYCGNAME/$$
        if [ "$i." != "cpuset." ]; then
          echo "$$" >> $CGROUPDIR/$i/$MYCGNAME/$$/tasks
        else
          echo "$CPUSETVAL" > $CGROUPDIR/$CPUSET/$MYCGNAME/$$/cpuset.cpus
          echo "0" > $CGROUPDIR/$CPUSET/$MYCGNAME/$$/cpuset.mems
          echo "$$" >> $CGROUPDIR/$CPUSET/$MYCGNAME/$$/tasks
        fi
      fi
    done

    mkdir $MYVARNAME/$MYCGNAME/$$
    echo "$*" > $MYVARNAME/$MYCGNAME/$$/commande
    date +%s > $MYVARNAME/$MYCGNAME/$$/timestamp
    echo $REPERT > $MYVARNAME/$MYCGNAME/$$/repertoire
    echo $ADDRMAIL > $MYVARNAME/$MYCGNAME/$$/mail
    echo $MYURL > $MYVARNAME/$MYCGNAME/$$/url

    # Je lance le job
    echo "OK:000:$$"
    # exec $*
    exec nohup $* &> stdout & #mod victorien
    exit 1
    ;;
  migre)
    shift
    if [ $# -lt 1 ]
    then
      fct_erreur 100:"$0 create [options] <PID>"
    fi
    AUTREARGU=1
    ADDRMAIL=""
    REPERT=$PWD
    MYURL=""
    let MAXCPU=NBCPU-1
    CPUSETVAL="0-$MAXCPU"
    while [ "$AUTREARGU." = "1." ]
    do
      AUTREARGU=0
      case "$1" in
       "-d")
          if [ $# -lt 3 ]
          then
            fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
          fi
          shift
          REPERT=$1
          cd $REPERT
          shift
          AUTREARGU=1
          ;;
       "-m")
          if [ $# -lt 3 ]
          then
            fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
          fi
          shift
          ADDRMAIL=$1
          shift
          AUTREARGU=1
          ;;
       "-u")
          if [ $# -lt 3 ]
          then
            fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
          fi
          shift
          MYURL=$1
          shift
          AUTREARGU=1
          ;;
       "-c")
          if [ $# -lt 3 ]
          then
            fct_erreur 100:"$0 create [options] <commande> <arg0> <arg1> ..."
          fi
          shift
          CPUSETVAL=$1
          shift
          AUTREARGU=1
          ;;
      esac
    done

    # Je cree un nouveau cgroup pour le job
    for i in $CONTROLNEED
    do
      if [ ! -d $CGROUPDIR/$i/$MYCGNAME/$1 ]; then
        mkdir $CGROUPDIR/$i/$MYCGNAME/$1
        if [ "$i." != "cpuset." ]; then
          echo "$1" >> $CGROUPDIR/$i/$MYCGNAME/$1/tasks
        else
          echo "$CPUSETVAL" > $CGROUPDIR/$CPUSET/$MYCGNAME/$1/cpuset.cpus
          echo "0" > $CGROUPDIR/$CPUSET/$MYCGNAME/$1/cpuset.mems
          echo "$1" >> $CGROUPDIR/$CPUSET/$MYCGNAME/$1/tasks
        fi
      fi
    done

    MACOMMANDE=$(ps h -p $1 -o cmd -www)
    mkdir $MYVARNAME/$MYCGNAME/$1
    echo "$MACOMMANDE" > $MYVARNAME/$MYCGNAME/$1/commande
    date +%s > $MYVARNAME/$MYCGNAME/$1/timestamp
    echo $REPERT > $MYVARNAME/$MYCGNAME/$1/repertoire
    echo $ADDRMAIL > $MYVARNAME/$MYCGNAME/$1/mail
    echo $MYURL > $MYVARNAME/$MYCGNAME/$1/url

    # Je lance le job
    echo "OK:000:$1"
#    exec "$*"
    exit 1
    ;;
  *)
    echo "<ordre> non reconnu ..."
    exit 999
    ;;
esac

#[ -z "$SCHEDFORK" ] && { \
#  SCHEDFORK=1\
#  echo "cgexec -g $CONTROLLIST:/$MYCGNAME/$$ $0 \"$@\""
#  #cgexec -g $CONTROLLIST:/$MYCGNAME $0 "$@" &\
#  exit;\
#}


