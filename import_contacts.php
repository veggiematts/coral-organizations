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
              // Does the organization already exists?
              $query = "SELECT count(*) AS count FROM $dbName.Organization WHERE UPPER(name) = '" . str_replace("'", "''", strtoupper($organizationName)) . "'";
              $result = $organization->db->processQuery($query, 'assoc');
              if ($result['count'] == 1) {
                
                $query = "SELECT name, organizationID FROM $dbName.Organization WHERE UPPER(name) = '" . str_replace("'", "''", strtoupper($organizationName)) . "'";
                $result = $organization->db->processQuery($query, 'assoc');
                $organizationID = $result['organizationID'];
                
                $c = new Contact();
                $c->organizationID = $organizationID;
                $c->name = $data[$cols['prenom']] . " " . $data[$cols['nom']];
                $c->lastUpdateDate = date( 'Y-m-d' );
                $c->emailAddress = $data[$cols['mail']];
                $c->phoneNumber = $data[$cols['telephone']];
                $c->faxNumber = $data[$cols['fax']];
                $c->noteText = $data[$cols['note']];
                $c->save();
                echo $data[$cols['societe']] . " saved \n";

              } else {
                echo "Warning: " . $result['count'] . " organization(s) found for " . $data[$cols['societe']] . "\n";
              }
 
    }
    $row++;
}
?>
