<?php

namespace Profondlib\Lanceur\Adapter;

use Profond\Entity\Machine;
use Profond\Entity\Project;
use Profond\Entity\User;

abstract class AbstractAdapter {

    public abstract function __construct(Project $Project, User $User, Machine $Machine, $UserConfigs, $ProjectConfigs);

    public abstract function checkCompatibility(Machine $Machine);

    public abstract function checkConnection($arrayConfig);
}
