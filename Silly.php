<?php

$users = [];

/**
 * This makes prompt secret and non-visible to users.
 *
 * @param string $prompt
 *
 * @return string
 */
function prompt_silent($prompt = "Enter Password:")
{
    if (preg_match('/^win/i', PHP_OS)) {
        $script = sys_get_temp_dir() . 'prompt_password.vbs';
        file_put_contents(
            $script,
            'wscript.echo(InputBox("'
            . addslashes($prompt)
            . '", "", "password here"))'
        );
        $command  = "cscript //nologo " . escapeshellarg($script);
        $password = rtrim(shell_exec($command));
        unlink($script);

        return $password;
    } else {
        $command = "/usr/bin/env bash -c 'echo OK'";
        if (rtrim(shell_exec($command)) !== 'OK') {
            trigger_error("Can't invoke bash");
        }
        $command  = "/usr/bin/env bash -c 'read -s -p \""
                    . addslashes($prompt)
                    . "\" mypassword && echo \$mypassword'";
        $password = rtrim(shell_exec($command));
        echo "\n";

        return $password;
    }
}

/**
 * Beep sound for mal-input.
 */
function beep()
{
    fprintf(STDOUT, "%s", "\x07");
}

/**
 * Main menu
 *
 * @param $users
 */
function likeToDo($users)
{
    print "\e[1;36m-----------------------------------\n";
    print "What would you like to do today?\n";
    print "1: Login\n";
    print "2: Register\n";
    print "3: Exit\n";

    $answer = readline("=>:");

    if ($answer === "1") {
        count($users) ? login($users) : print "--------------------------\n There are no users yet!\n";

        beep();
        sleep(1);
        likeToDo($users);
    } elseif ($answer === "2") {
        register($users);
    } elseif ($answer === "3") {
        exit;
    } else {
        print "**********************************\n";
        print "Incorrect input, please try again!\n";
        print "**********************************\n";

        beep();
        sleep(1);
        likeToDo($users);
    }
}

function login($users)
{
    print "--------------\n";
    print "Input username\n";
    $answer = readline("=>:");

    foreach ($users as $user) {
        if ($user->username === $answer) {
            print "--------------\n";
            print "Input password\n";
            $password = prompt_silent("=>:");

            if ($password === $user->password) {
                menu($users, $user);
            } else {
                print "*******************\n";
                print "Incorrect password!\n";
                print "*******************\n";

                beep();
                sleep(1);
                likeToDo($users);
            }
        }
    }
    print "********************\n";
    print "User does not exist!\n";
    print "********************\n";

    beep();
    sleep(1);
    likeToDo($users);
}

function register($users)
{
    print "-----------------------\n";
    print "Input desired username.\n";
    $username = readline("=>:");

    foreach ($users as $user) {
        if ($username === $user->username) {
            print "***************\n";
            print "Username taken!\n";
            print "***************\n";

            beep();
            sleep(1);
            likeToDo($users);
        }
    }

    $user = new stdClass();

    if ($username) {
        $user->username = $username;
    } else {
        print "************************\n";
        print "Username cannot be empty\n";
        print "************************\n";

        beep();
        sleep(1);
        likeToDo($users);
    }

    print "------------------\n";
    print "Input new password\n";
    $password = prompt_silent("=>:");

    if ($password) {
        print "-----------------------\n";
        print "Please confirm password\n";
        $passwordConfirmation = prompt_silent("=>:");

        if ($password === $passwordConfirmation) {
            $user->password = $password;
            $user->name     = "unassigned";
            $user->number   = "unassigned";
            $user->email    = "unassigned";
            $user->contacts = [];

            print "-------------\n";
            print "User Created!\n";
            sleep(1);

            $users[] = $user;
            likeToDo($users);
        } else {
            print "***********************\n";
            print "Passwords do not match!\n";
            print "***********************\n";

            beep();
            sleep(1);
            likeToDo($users);
        }
    } else {
        print "************************\n";
        print "Password cannot be empty\n";
        print "************************\n";

        beep();
        sleep(1);
        likeToDo($users);
    }
}

/**
 * User menu
 *
 * @param $users
 * @param $user
 */
function menu($users, $user)
{
    print "\e[1;32m What would you like to do?\n";
    print "1: Access Account\n";
    print "2: View Contacts\n";
    print "3: Add Contact\n";
    print "4: Edit Contact\n";
    print "5: Delete Contact\n";
    print "6: Logout\n";

    $answer = readline("=>:");

    if ($answer === "1") {
        account($users, $user);
    } elseif ($answer === "2") {
        contactView($users, $user);
    } elseif ($answer === "3") {
        contactAdd($users, $user);
    } elseif ($answer === "4") {
        contactEdit($users, $user);
    } elseif ($answer === "5") {
        contactDelete($users, $user);
    } elseif ($answer === "6") {
        likeToDo($users);
    } else {
        print "********************************\n";
        print "Invalid input. Please try again!\n";
        print "********************************\n";

        beep();
        sleep(1);
        menu($users, $user);
    }
}

/**
 * User account menu
 *
 * @param $users
 * @param $user
 */
function account($users, $user)
{
    print "\e[1;33m Type \"back\" to go back.\n";
    print "What would you like to do?\n";
    print "1: Update Name\n";
    print "2: Update Number\n";
    print "3: Update Email\n";
    print "4: Account Info\n";
    print "5: Logout\n";

    $answer = readline("=>:");
    checkAnswer($answer, $users, $user);

    if ($answer === "1") {
        newName($users, $user);
    } elseif ($answer === "2") {
        newNumber($users, $user);
    } elseif ($answer === "3") {
        newEmail($users, $user);
    } elseif ($answer === "4") {
        accountCheck($users, $user);
    } elseif ($answer === "5") {
        likeToDo($users);
    } else {
        print "**************************\n";
        print "Wrong-o! Please try again!\n";
        print "**************************\n";

        beep();
        sleep(1);
        account($users, $user);
    }
}

function newName($users, $user)
{
    print "-----------------------------------\n";
    print "Your current name is $user->name!\n";
    print "What would you like your name to be?\n";
    $answer     = readline("=>:");
    $user->name = $answer;

    print "-----------------------------------\n";
    print "Update successful!\n";
    print "Your new name is $user->name!\n";
    print "-----------------------------------\n";

    sleep(1);
    account($users, $user);
}

function newNumber($users, $user)
{
    print "-----------------------------------\n";
    print "Your current number is $user->number!\n";
    print "What would you like your number to be?\n";
    $answer = readline("=>:");

    if (is_numeric($answer)) {
        $user->number = $answer;

        print "-----------------------------------\n";
        print "Update successful!\n";
        print "Your new number is $user->number!\n";
        print "-----------------------------------\n";

        sleep(1);
        account($users, $user);
    }
}

function newEmail($users, $user)
{
    print "-----------------------------------\n";
    print "Your current email is $user->email!\n";
    print "What would you like your email to be?\n";
    $answer      = readline("=>:");
    $user->email = $answer;

    print "-----------------------------------\n";
    print "Update successful!\n";
    print "Your new email is $user->email!\n";
    print "-----------------------------------\n";

    sleep(1);
    account($users, $user);
}

function accountCheck($users, $user)
{
    print "-----------------------------------\n";
    print "-----------------------------------\n";
    print "Name:   $user->name\n";
    print "Number: $user->number\n";
    print "Email:  $user->email\n";
    print "-----------------------------------\n";

    sleep(2);
    account($users, $user);
}

function contactView($users, $user)
{
    checkForContacts($users, $user);

    print "\e[1;35mThese are your current contacts:\n";
    print "--------------------------------\n";
    foreach ($user->contacts as $contact) {
        print "Name:   $contact->name\n";
        print "Number: $contact->number\n";
        print "Email:  $contact->email\n";
        print "----------------------------\n";
    }

    sleep(2);
    menu($users, $user);
}

/**
 * Checks the answer for any desire to backtrack, returns user to main menu.
 *
 * @param $answer
 * @param $users
 * @param $user
 */
function checkAnswer($answer, $users, $user)
{
    if (strtolower($answer === "exit")) {
        print "Returning to menu...\n";
        print "--------------------\n";
        sleep(1);
        menu($users, $user);
    } elseif (strtolower($answer === "back")) {
        print "Returning to menu...\n";
        print "--------------------\n";
        sleep(1);
        menu($users, $user);
    } elseif (strtolower($answer === "quit")) {
        print "Returning to menu...\n";
        print "--------------------\n";
        sleep(1);
        menu($users, $user);
    } elseif (strtolower($answer === "return")) {
        print "Returning to menu...\n";
        print "--------------------\n";
        sleep(1);
        menu($users, $user);
    } elseif (strtolower($answer === "menu")) {
        print "Returning to menu...\n";
        print "--------------------\n";
        sleep(1);
        menu($users, $user);
    } elseif (strtolower($answer === "this app sucks")) {
        print "You think you're so funny, but you aint...\n";
        print "-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_-_\n";
        sleep(5);
        menu($users, $user);
    }
}

/**
 * Checks if there are any contacts before allowing to proceed. Returns user to main menu.
 *
 * @param $users
 * @param $user
 */
function checkForContacts($users, $user)
{
    $contacts = array_filter($user->contacts);
    if (empty($contacts)) {
        print "****************************\n";
        print "You don't have any contacts!\n";
        print "****************************\n";

        beep();
        sleep(1);
        menu($users, $user);
    }
}

function contactAdd($users, $user)
{
    print "Contact name:\n";
    $name = readline("=>:");
    checkAnswer($name, $users, $user);
    if ($name === "") {
        print "********************\n";
        print "Name cannot be blank\n";
        print "********************\n";
        beep();
        sleep(1);
        contactAdd($users, $user);
    }
    foreach ($user->contacts as $contact) {
        $contactName = "$contact->name";
        if (strtolower($name) === strtolower($contactName)) {
            print "**************************************\n";
            print "Contact with that name already exists!\n";
            print "**************************************\n";
            beep();
            sleep(1);
            contactAdd($users, $user);
        }
    }

    print "Contact number:\n";
    $number = readline("=>:");
    checkAnswer($number, $users, $user);
    if (strtolower($number) === "skip" || strtolower($number) === "") {
        $number = "Unassigned";
    }

    print "Contact email:\n";
    $email = readline("=>:");
    checkAnswer($email, $users, $user);
    if (strtolower($email) === "skip" || strtolower($email) === "") {
        $email = "Unassigned";
    }

    $contact          = new stdClass();
    $contact->name    = $name;
    $contact->number  = $number;
    $contact->email   = $email;
    $user->contacts[] = $contact;

    print "----------------\n";
    print "Contact Created!\n";
    print "----------------\n";

    sleep(1);
    menu($users, $user);
}

function contactEdit($users, $user)
{
    checkForContacts($users, $user);

    print "\e[0;37m-------------------------------------\n";
    foreach ($user->contacts as $contact) {
        print "* $contact->name\n";
    }
    print "-------------------------------------\n";
    print "Which contact would you like to edit?\n";

    $contactToEdit = readline("=>:");
    checkAnswer($contactToEdit, $users, $user);

    foreach ($user->contacts as $contact) {
        $contactName = "$contact->name";
        if (strtolower($contactToEdit) === strtolower($contactName)) {
            $contactToEdit = $contact;
        } elseif (strtolower($contactToEdit) !== strtolower($contactName)) {
            print "**********************\n";
            print "Contact doesn't exist!\n";
            print "**********************\n";
            beep();
            sleep(1);
            contactEdit($users, $user);
        } else {
            print "********************\n";
            print "Something went wrong\n";
            print "********************\n";

            beep();
            sleep(2);
            contactEdit($users, $user);
        }
    }

    print "------------------------------\n";
    print "You have selected $contactToEdit->name\n";
    sleep(1);
    edit($contactToEdit, $users, $user);
}

function edit($contactToEdit, $users, $user)
{
    print "What would you like to do?\n";
    print "Type \"back\" to go back\n";
    print "------------------------------\n";
    print "1: Edit name\n";
    print "2: Edit number\n";
    print "3: Edit email\n";
    $answer = readline("=>:");
    checkAnswer($answer, $users, $user);
    if ($answer === "1" || strtolower($answer) === "name") {
        print "Current name is $contactToEdit->name\n";
        sleep(0.5);

        print "Type new name\n";
        $newName = readline("=>:");
        checkAnswer($newName, $users, $user);
        
        $contactToEdit->name = $newName;
    } elseif ($answer === "2" || strtolower($answer) === "number") {
        print "Current number is $contactToEdit->number\n";
        sleep(0.5);

        print "Type new number\n";
        $newNumber = readline("=>:");
        checkAnswer($newNumber, $users, $user);

        if (strtolower($newNumber) === "skip" || strtolower($newNumber) === "") {
            $newNumber = "Unassigned";
        }

        $contactToEdit->number = $newNumber;
    } elseif ($answer === "3" || strtolower($answer) === "email") {
        print "Current email is $contactToEdit->email\n";
        sleep(0.5);

        print "Type new email\n";
        $newEmail = readline("=>:");
        checkAnswer($newEmail, $users, $user);

        if (strtolower($newEmail) === "skip" || strtolower($newEmail) === "") {
            $newEmail = "Unassigned";
        }

        $contactToEdit->email = $newEmail;
    } else {
        print "********************\n";
        print "Something went wrong\n";
        print "********************\n";

        beep();
        sleep(2);
        contactEdit($users, $user);
    }

    print "----------------\n";
    print "Contact Updated!\n";
    print "----------------\n";

    sleep(1);
    edit($contactToEdit, $users, $user);
}

function contactDelete($users, $user)
{
    checkForContacts($users, $user);

    print "\e[01;31m---------------------------------------\n";
    foreach ($user->contacts as $contact) {
        print "* $contact->name\n";
    }
    print "---------------------------------------\n";
    print "Which contact would you like to delete?\n";

    $contactToDelete = readline("=>:");
    checkAnswer($contactToDelete, $users, $user);
    foreach ($user->contacts as $contact) {
        $contactName = "$contact->name";
        if (strtolower($contactToDelete) === strtolower($contactName)) {
            $contactToDelete = $contact;
        } elseif (strtolower($contactToDelete) !== strtolower($contactName)) {
            print "**********************\n";
            print "Contact doesn't exist!\n";
            print "**********************\n";
            beep();
            sleep(1);
            contactEdit($users, $user);
        } else {
            print "********************\n";
            print "Something went wrong\n";
            print "********************\n";

            beep();
            sleep(2);
            contactEdit($users, $user);
        }
    }

    print "------------------------------------------\n";
    print "Are you sure you want to delete $contactToDelete->name?\n";
    print "1: Yes\n";
    print "2: No\n";

    $answer = readline("=>:");
    checkAnswer($answer, $users, $user);
    if ($answer === "1" || strtolower($answer) === "yes") {
        $key = array_search($contactToDelete, $user->contacts, true);
        unset($user->contacts[$key]);
        print "*****************\n";
        print "Contact Deleted!!\n";
        print "*****************\n";

        sleep(1);
        menu($users, $user);
    } elseif ($answer === "2" || strtolower($answer) === "no") {
        contactDelete($users, $user);
    } else {
        print "***************\n";
        print "Incorrect Input\n";
        print "***************\n";

        beep();
        sleep(1);
        contactDelete($users, $user);
    }
}

likeToDo($users);