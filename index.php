<pre>
<?php
require_once "JsonDB.php";

$db = new JsonDB(__DIR__ . '/db');

echo '<h3>Insert user:</h3>';
try
{
    $db->insert('users', ['first_name' => 'Mojtaba', 'last_name' => 'Lessmore', 'country' => 'Iran']);
    $db->insert('users', ['first_name' => 'Alex', 'last_name' => 'Morphy', 'country' => 'UK']);
    echo '2 new users inserted.';
}
catch (Exception $e)
{
    echo $e;
}
echo '<hr/>';
echo '<h3>Select all users:</h3>';
try
{
    $users = $db->select('users');
    print_r($users);
}
catch (Exception $e)
{
    echo $e;
}
echo '<hr/>';
echo "<h3>Select specified user: <code>['first_name' => 'Mojtaba']</code></h3>";
try
{
    $users = $db->select('users', ['first_name' => 'Mojtaba']);
    print_r($users);
}
catch (Exception $e)
{
    echo $e;
}
echo '<hr/>';
echo "<h3>Update specified user: find user <code> ['first_name' => 'Mojtaba', 'last_name' => 'Lessmore']</code> update to ['last_name' => 'Bahrami']</h3>";
try
{
    $db->update('users', ['last_name' => 'Bahrami'], ['first_name' => 'Mojtaba', 'last_name' => 'Lessmore']);
    $users = $db->select('users', ['first_name' => 'Mojtaba']);
    print_r($users);
}
catch (Exception $e)
{
    echo $e;
}

echo '<hr/>';
echo "<h3>Update all users: <code>['country' => 'Switzerland']</code></h3>";
try
{
    $db->update('users', ['country' => 'Switzerland']);
    $users = $db->select('users');
    print_r($users);
}
catch (Exception $e)
{
    echo $e;
}
echo '<hr/>';
echo "<h3>Delete specified user: <code>['last_name' => 'Morphy']</code></h3>";
try
{
    $db->delete('users', ['last_name' => 'Morphy']);
    $users = $db->select('users');
    print_r($users);
}
catch (Exception $e)
{
    echo $e;
}

echo '<hr/>';
echo '<h3>Delete all users:</h3>';
try
{
    $db->delete('users');
    $users = $db->select('users');
    print_r($users);
}
catch (Exception $e)
{
    echo $e;
}
