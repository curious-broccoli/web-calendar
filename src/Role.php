<?php

enum Role: int {
    case Default = 10;
    case Approver = 20;
    case Moderator = 30;
}

/**
 * Returns the Role of the given user
 *
 * every user except guest must have a Role so it always returns a valid Role
 * don't call this with the guest's id
 */
function get_user_role(int $id, PDO $dbh) : Role {
    $stmt = $dbh->prepare("SELECT role FROM user WHERE userid = :userid;");
    $stmt->execute(array(":userid" => $id));
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return Role::from($result["role"]);
}

/**
 * exits the script and returns error status if the user doesn't have
 * one of the allowed roles
 *
 * @param Role[] $allowed_roles
 */
function block_unauthorized(array $allowed_roles, PDO $dbh): void {
    if (!isset($_SESSION["userid"])) {
        http_response_code(401);
        die("401 Unauthorized");
    } else {
        $role = get_user_role($_SESSION["userid"], $dbh);
        if (!in_array($role, $allowed_roles)) {
            http_response_code(403);
            die("403 Forbidden");
        }
    }
}

?>