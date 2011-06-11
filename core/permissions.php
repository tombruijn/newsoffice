<?php
//Permissions are not supposed to be added to this file
//plugin permission have their own file in the info directory
$default_permissions = array('comments','newsoffice','dashboard-notes','users-main','users-profile-own','support-main');

$no_sec = array('news-page','main','manager','settings','users','support');

$sec = 'news-page';
$no_perm_info[$sec] = array('News page(s)','Allow users to use features from your news page.');
$no_perm[$sec][] = array('Place comments.','Allow users to place comments on every news post.',array('comments'));

$sec = 'main';
$no_perm_info[$sec] = array('NewsOffice main','Allow users to login to the Administration Panel of your installation of NewsOffice.');
$no_perm[$sec][] = array('Main access.','Give users in this group main access, to this application. This one needs be active for the permissions below to work.',array('newsoffice'));
$no_perm[$sec][] = array('Notes','Allow users to read and write the notes.',array('dashboard-notes'));

$sec = 'manager';
$no_perm_info[$sec] = array('Manager','Give users acces to the manager section. Here they can manage news, categories, comments and uploads.');
$no_perm[$sec][] = array('Main access','Allow users to the manager section, this is required for the permissions below to work.',array('manager-main'));
$no_perm[$sec][] = array('News manager','Allow users to create, edit and delete your news.',array('manager-news','editor-news'));
$no_perm[$sec][] = array('Category manager','Allow users to create, edit and delete your categories.',array('manager-categories','editor-category'));
$no_perm[$sec][] = array('Comment manager','Allow users to edit and delete placed comments.',array('manager-comments','editor-comments'));
$no_perm[$sec][] = array('Upload manager','Allow users to edit and delete uploaded files.',array('manager-uploads','editor-uploads'));

$sec = 'settings';
$no_perm_info[$sec] = array('Settings','Acces to settings section.');
$no_perm[$sec][] = array('Main access & Edit settings','Allow users to the settings section, this is required for the permissions below to work. This permission also allow users to edit all integrated NewsOffice settings.',array('settings-main'));
//$no_perm[$sec][] = array('Edit settings','Edit main settings, like the url, date and tim format. Must be active for the other settings permissions to work.',array('settings-main'));
$no_perm[$sec][] = array('Placement','Acces to form that generates the code you will have to use to display your news page',array('settings-integration'));
$no_perm[$sec][] = array('Template manager','Change the templates that are used in your news page(s).',array('settings-themes','settings-themes-editor'));
$no_perm[$sec][] = array('Back-up','Ability to back-up your news, etc.',array('settings-back-up'));
$no_perm[$sec][] = array('Import','Ability to import your previously back-up\'s',array('settings-import'));

$sec = 'users';
$no_perm_info[$sec] = array('Users','Allow users to edit their profiles and that from others.');
$no_perm[$sec][] = array('Main access','Allow users to the users section, this is required for the permissions below to work.',array('users-main'));
$no_perm[$sec][] = array('Edit own profile','Allow users to manage to edit own profile.',array('users-profile-own'));
$no_perm[$sec][] = array('Manage users','Access to user manager.',array('users-manager-users'));
$no_perm[$sec][] = array('Create users','Create a new user account, needs to Manage users permission.',array('users-create-user'));
$no_perm[$sec][] = array('Edit other profiles','Edit the profiles of other users.',array('users-profile'));
$no_perm[$sec][] = array('Manage groups','Edit user groups details.',array('users-manager-groups','users-editor-groups'));
$no_perm[$sec][] = array('Create groups','Create new user groups, needs the Manage groups permission.',array('users-create-group'));
$no_perm[$sec][] = array('Edit group permissions','Change the permissions of the group, not allowed for own group and root group. This needs the Manage groups permission',array('users-groups-permissions'));

$sec = 'support';
$no_perm_info[$sec] = array('Support','Allow users to view the current state of NewsOffice and view the documentation, if any.');
$no_perm[$sec][] = array('Main access','Allow users to access the support section, this is required for the permissions below to work.',array('support-main'));
$no_perm[$sec][] = array('Manuals','Allow users to read manuals, so they can learn more about how to use NewsOffice.',array('manuals-main','manuals-read'));
$no_perm[$sec][] = array('System status','Allow users to view the current state of NewsOffice, important information about NewsOffice is shown here.',array('system-status'));
$no_perm[$sec][] = array('Updater','Allow users to check for updates for NewsOffice. Information about your version of NewsOffice is shown here. Do not allow this if you don\'t want users to view important information.',array('updater'));
?>