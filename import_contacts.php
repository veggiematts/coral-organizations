<?php

include_once 'directory.php';

$util = new Utility();
$config = new Configuration();
$filename = $argv[1];
$delimiter = "\t";

$handle = fopen($filename, "r");
$row = 0;

while (($data = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
    if ($row == 0) {
        foreach ($data as $key => $value) {
            $cols[strtolower(str_replace(' ', '_', $value))] = $key;
        }
    } else {
              $dbName = $config->settings->organizationsDatabaseName;
              $organization = new Organization();
              $organizationName = $data[$cols['societe']];
              $contactRoleName = $data[$cols['contact_type']];

              // Does the organization already exists?
              $query = "SELECT count(*) AS count FROM $dbName.Organization WHERE UPPER(name) = '" . str_replace("'", "''", strtoupper($organizationName)) . "'";
              $result = $organization->db->processQuery($query, 'assoc');
              if ($result['count'] > 1) {
                echo "Warning: " . $result['count'] . " organization(s) found for " . $data[$cols['societe']] . "\n";
                $organizationID = null;
              } else {
                if ($result['count'] == 0) {
                    $o = new Organization();
                    $o->name = $organizationName;
                    $o->save();
                    $organizationID = $o->organizationID;
                    echo "Organization $organizationName ($organizationID) created\n";

                }
                if ($result['count'] == 1) {
                    $query = "SELECT name, organizationID FROM $dbName.Organization WHERE UPPER(name) = '" . str_replace("'", "''", strtoupper($organizationName)) . "'";
                    $result = $organization->db->processQuery($query, 'assoc');
                    $organizationID = $result['organizationID'];
                }
             }

             // Does the contact role already exists?
             $query = "SELECT count(*) AS count FROM $dbName.ContactRole WHERE UPPER(shortName) = '" . str_replace("'", "''", strtoupper($contactRoleName)) . "'";
             $result = $organization->db->processQuery($query, 'assoc');
             if ($result['count'] > 1) {
                echo "Warning: " . $result['count'] . " ContactRoles found for " . $data[$cols['contact_type']] . "\n";
                $contactRoleID = null;
             } else {
                if ($result['count'] == 0) {
                    $o = new ContactRole();
                    $o->shortName = $contactRoleName;
                    $o->save();
                    $contactRoleID = $o->contactRoleID;
                    echo "ContactRole $contactRoleName ($contactRoleID) created\n";

                }
                if ($result['count'] == 1) {
                    $query = "SELECT contactRoleID FROM $dbName.ContactRole WHERE UPPER(shortName) = '" . str_replace("'", "''", strtoupper($contactRoleName)) . "'";
                    $result = $organization->db->processQuery($query, 'assoc');
                    $contactRoleID = $result['contactRoleID'];
                }
             }

            // Creating contact 
            $c = new Contact();
            $c->organizationID = $organizationID;
            $c->name = $data[$cols['prenom']] . " " . $data[$cols['nom']];
            $c->lastUpdateDate = date( 'Y-m-d' );
            $c->emailAddress = $data[$cols['mail']];
            $c->phoneNumber = $data[$cols['telephone']];
            $c->faxNumber = $data[$cols['fax']];
            $c->noteText = $data[$cols['note']];
            $c->save();

            // Associating Contact and ContactRole
            $crp = new ContactRoleProfile();
            $crp->contactRoleID = $contactRoleID;
            $crp->contactID = $c->contactID;
            $crp->save();
            echo $data[$cols['prenom']] . " " . $data[$cols['nom']] . " - " . $data[$cols['societe']] . " (" . $c->contactID . ") saved and associated to role $contactRoleName ($contactRoleID) \n";

    }
    $row++;
}
?>
