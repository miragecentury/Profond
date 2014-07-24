apt-get -y install curl
apt-get -y install rc
apt-get -y install cgroup-lite
apt-get -y install build-essential cmake flex bison zlib1g-dev qt4-dev-tools 
apt-get -y install libqt4-dev gnuplot libreadline-dev libncurses-dev libxt-dev 
apt-get -y install libopenmpi-dev openmpi-bin libboost-system-dev libboost-thread-dev libgmp-dev libmpfr-dev

cd ~
mkdir OpenFOAM
cd OpenFOAM
wget "http://downloads.sourceforge.net/foam/OpenFOAM-2.3.0.tgz?use_mirror=mesh" -O OpenFOAM-2.3.0.tgz
wget "http://downloads.sourceforge.net/foam/ThirdParty-2.3.0.tgz?use_mirror=mesh" -O ThirdParty-2.3.0.tgz
 
tar -xzf OpenFOAM-2.3.0.tgz 
tar -xzf ThirdParty-2.3.0.tgz

ln -s /usr/bin/mpicc.openmpi OpenFOAM-2.3.0/bin/mpicc
ln -s /usr/bin/mpirun.openmpi OpenFOAM-2.3.0/bin/mpirun

uname -m

. $HOME/OpenFOAM/OpenFOAM-2.3.0/etc/bashrc WM_NCOMPPROCS=4 WM_MPLIB=SYSTEMOPENMP

echo "alias of230='source \$HOME/OpenFOAM/OpenFOAM-2.3.0/etc/bashrc $FOAM_SETTINGS'" >> $HOME/.bashrc

chmod +x ~/OpenFOAM/OpenFOAM-2.3.0/etc/bashrc
. ~/OpenFOAM/OpenFOAM-2.3.0/etc/bashrc

cd $WM_THIRD_PARTY_DIR
 
# This next command will take a while... somewhere between 5 minutes to 30 minutes.
./Allwmake > make.log 2>&1
 
#update the shell environment
wmSET $FOAM_SETTINGS

#First make very certain that the correct Qt version is being used, by running this command:
export QT_SELECT=qt4
 
#this will take a while... somewhere between 30 minutes to 2 hours or more
./makeParaView4 > log.makePV 2>&1
 
#update the shell environment
wmSET $FOAM_SETTING

#Go into OpenFOAM's main source folder
cd $WM_PROJECT_DIR
 
#Still better be certain that the correct Qt version is being used
export QT_SELECT=qt4
 
# This next command will take a while... somewhere between 30 minutes to 3-6 hours.
./Allwmake > make.log 2>&1
 
#Run it a second time for getting a summary of the installation
#./Allwmake > make.log 2>&1

#Check if icoFoam is working
icoFoam -help
 
#Edit the file "make.log" and check if there are any error messages
#Example:
gedit make.log
 
#Create a tarball in case you've seen any errors (it's the first error that matters)
#or if you don't understand the output
#and attach the file "make.log.tar.gz" to a post in the designated thread
tar -czf make.log.tar.gz make.log

chmod +x ~/profondui/sched.sh
~/profondui/sched.sh install root

curl -H "Content-Type: application/json" -d '{"idmachine":"%profond_idmachine%","key":"%profond_keymachine%"}' http://%profond_url%/api/isready/%profond_idmachine%