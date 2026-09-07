<?php
function getMyGroups(){
    $groups = array();
    return $groups;
}

function getGravatarUrl($email)
{
    $emailHash = hash('sha256', strtolower(trim($email)));
    return "https://www.gravatar.com/avatar/$emailHash";
}
?>