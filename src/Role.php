<?php

enum Role: int {
    case Default = 10;
    case Approver = 20;
    case Moderator = 30;
}

function get_user_role(int $id, PDO $dbh) : Role {
    /**
     * Returns the Role of the given user
     * every user except guest must have a Role so it always returns a valid Role
     * don't call this with the guest's id
     */
    $stmt = $dbh->prepare("SELECT role FROM user WHERE userid = :userid;");
    $stmt->execute(array(":userid" => $id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return Role::from($result["role"]);
}

?>