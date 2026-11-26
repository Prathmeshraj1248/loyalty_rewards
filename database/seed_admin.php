<?php
declare(strict_types=1);

use App\Config\Database;
use App\Config\Env;
use App\Repositories\UserRepository;

// Never runnable from a browser.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require __DIR__ . '/../vendor/autoload.php';
Env::load(__DIR__ . '/../.env');

$pdo = Database::connection();

// Assumption #9: exactly one owner admin, so refuse to create a second.
if ((int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn() > 0) {
    fwrite(STDERR, "An admin already exists. Nothing was changed.\n");
    exit(1);
}

function ask(string $label): string
{
    echo $label . ': ';
    return trim((string) fgets(STDIN));
}

$name     = ask('Admin name');
$email    = strtolower(ask('Admin email'));
$password = ask('Admin password (min 10 characters, visible while typing)');

if (mb_strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 10 || strlen($password) > 72) {
    fwrite(STDERR, "Invalid input. Name 2+ chars, valid email, password 10 to 72 chars.\n");
    exit(1);
}

$id = (new UserRepository($pdo))->create($name, $email, password_hash($password, PASSWORD_DEFAULT), 'admin');
echo "Admin created with id {$id}.\n";