<?php

function importUsers($users, $existing, $httpClient) {

  $created = $updated = $emails = [];

  foreach($users as $user) {

    // email is mandatory for contact to be persisted
    // hence, checking is not empty in column
    if($user[2] == "") {
      continue;
    }

    $company = !empty($user[10]) ? ' (' . $user[10] . ')' : ' ';
    $cp = !empty($user[6]) ? $user[6] : " ";
    $city = !empty($user[5]) ? $user[5] : " ";

    $contact = [
        'firstname' => $user[0],
        'lastname' => $user[1]. $company,
        'fullname' => $user[0] . ' ' . $user[1] . $company,
        'email' => $user[2],
        'city' => !empty($user[3]) ? $user[3] : " ",
        'street' => !empty($user[4]) ? $user[4] : " ",
        'region' => $city,
        'postcode' => $cp,
        'country' => !empty($user[7]) ? $user[7] : " ",
        'fulladdress' => !empty($user[4]) ? $user[4] . " - " . $city . " - CP: " . $cp : " ",
        'work_phone' => !empty($user[8]) ? $user[8] : " ",
        'home_phone' => !empty($user[9]) ? $user[9] : " ",
        'organization' => !empty($user[10]) ? $user[10] : " ",
        'department' => !empty($user[11]) ? $user[11] : " "
    ];

    $emails[] = $user[2];

    if (!array_key_exists($contact['email'], $existing)) {
        $xml = createContactXML($contact);
        $response = $httpClient->post('https://www.google.com/m8/feeds/contacts/servicargointernacional.com/full', ['body' => $xml]);

      if($response->getStatusCode() == 201) {
        $created[] = $contact['email'];
      } else {
        $created[] = "ERROR: " . $contact['email'];
      }

    } else {
      $id = explode("/", $existing[$contact['email']][0])[8];
      $version = explode("/", $existing[$contact['email']][0])[9];
      $xml = editContactXML($contact, $id, $version);
      $response = $httpClient->put("https://www.google.com/m8/feeds/contacts/servicargointernacional.com/base/" . $id . "/" . $version, ['body' => $xml]);

      if($response->getStatusCode() == 200) {
        $updated[] = $contact['email'];
      } else {
        $updated[] = "ERROR: " . $contact['email'];
      }
    }

    $xmlResponse = $response->getBody()->getContents();
    sleep(1);
  }

  $deleted = removeUsers($emails, $existing, $httpClient);

  echo "<p style='margin-left:20px; color:orange; font-weight:bold'>ACTUALIZADOS:</p>";
  foreach($updated as $up) {
    echo "<p style='margin-left:30px'>".$up."</p>";
  }

  echo "<p style='margin-left:20px; color:green; font-weight:bold'>CREADOS:</p>";
  foreach($created as $cr) {
    echo "<p style='margin-left:30px'>".$cr."</p>";
  }

  echo "<p style='margin-left:20px; color:red; font-weight:bold'>BORRADOS:</p>";
  foreach($deleted as $de) {
    echo "<p style='margin-left:30px'>".$de."</p>";
  }
}

function removeUsers($emails, $existing, $httpClient) {
  $deleted = [];
  foreach($existing as $email => $values) {

    if (!in_array($email, $emails)) {

        $id = explode("/", $values[0])[8];
        $version = explode("/", $values[0])[9];

        $xml = deleteContactXML($id, $version);
        $response = $httpClient->delete("https://www.google.com/m8/feeds/contacts/servicargointernacional.com/base/" . $id . "/" . $version, ['body' => $xml]);
        $deleted[] = $email;
    }
  }
  return $deleted;
}

function parseXML($xml) {
    $xmlParser = new XMLReader();
    $xmlParser->xml($xml);
    $users = [];

    while ($xmlParser->read()) {
        $value = $xmlParser->readInnerXML();
        $name = $xmlParser->name;
        $nodeType = $xmlParser->nodeType;

        if ($name == 'id' && $nodeType == '1') {
            $baseLink = $value;
        }

        if ($name == 'title' && $nodeType == '1') {
            $fullName = $value;
        }

        if ($name == 'link') {
            if ($xmlParser->getAttribute('rel') == 'edit') {
                $editLink = $xmlParser->getAttribute('href');
            }
        }

        if ($name == "gd:email") {
            $email = $xmlParser->getAttribute('address');
            $users[$email] = array($editLink, $baseLink, $fullName);
        }
    }
    
    $xmlParser->close();
    return $users;
}

function deleteContactXML($id, $version) {
    return "
        <entry>
          <id>https://www.google.com/m8/feeds/contacts/servicargointernacional.com/base/".$id."/".$version."</id>
        </entry>
    ";
}


function editContactXML($user, $id, $version) {
    return str_replace("&", "y", "
        <entry xmlns='http://www.w3.org/2005/Atom' xmlns:batch='http://schemas.google.com/gdata/batch' xmlns:gContact='http://schemas.google.com/contact/2008' xmlns:gd='http://schemas.google.com/g/2005'>
           <id>http://www.google.com/m8/feeds/contacts/servicargointernacional.com/base/".$id."</id>
           <updated>2020-04-24T20:46:09.383Z</updated>
           <category scheme='http://schemas.google.com/g/2005#kind' term='http://schemas.google.com/contact/2008#contact' />
           <title type='text'>".$user['fullname']."</title>
           <category term='user-tag' label='Directorio compartido'/>
           <content type='text'>Notes</content>
           <link rel='self' type='application/atom+xml' href='https://www.google.com/m8/feeds/contacts/servicargointernacional.com/full/".$id."' />
           <link rel='edit' type='application/atom+xml' href='https://www.google.com/m8/feeds/contacts/servicargointernacional.com/full/".$id."/".$version."' />
           <gd:email rel='http://schemas.google.com/g/2005#work' address='".$user['email']."' primary='true' displayName='".$user['fullname']."' />
           <gd:name>
             <gd:givenName>".$user['firstname']."</gd:givenName>
             <gd:familyName>".$user['lastname']."</gd:familyName>
             <gd:fullName>".$user['fullname']."</gd:fullName>
          </gd:name>
          <gd:organization rel='http://schemas.google.com/g/2005#work'>
            <gd:orgName>".$user['organization']."</gd:orgName>
            <gd:orgDepartment>".$user['department']."</gd:orgDepartment>
          </gd:organization>
           <gd:phoneNumber rel='http://schemas.google.com/g/2005#work' primary='true'>".$user['work_phone']."</gd:phoneNumber>
           <gd:phoneNumber rel='http://schemas.google.com/g/2005#home'>".$user['home_phone']."</gd:phoneNumber>
           <gd:postalAddress rel='http://schemas.google.com/g/2005#work' primary='true'>".$user['fulladdress']."</gd:postalAddress>
        </entry>
    ");
}


function createContactXML($user) {
    return str_replace("&", "y", "
        <atom:entry xmlns:atom='http://www.w3.org/2005/Atom'
            xmlns:gd='http://schemas.google.com/g/2005'>
          <atom:category scheme='http://schemas.google.com/g/2005#kind'
            term='http://schemas.google.com/contact/2008#contact' />
          <gd:name>
             <gd:givenName>".$user['firstname']."</gd:givenName>
             <gd:familyName>".$user['lastname']."</gd:familyName>
             <gd:fullName>".$user['fullname']."</gd:fullName>
          </gd:name>
          <gd:organization rel='http://schemas.google.com/g/2005#work'>
            <gd:orgName>".$user['organization']."</gd:orgName>
            <gd:orgDepartment>".$user['department']."</gd:orgDepartment>
          </gd:organization>
          <atom:content type='text'>Notes</atom:content>
          <category term='user-tag' label='Directorio compartido'/>
          <gd:email rel='http://schemas.google.com/g/2005#work'
            primary='true'
            address='".$user['email']."' displayName='".$user['fullname']."' />
          <gd:phoneNumber rel='http://schemas.google.com/g/2005#work'
            primary='true'>
            ".$user['work_phone']."
          </gd:phoneNumber>
          <gd:phoneNumber rel='http://schemas.google.com/g/2005#home'>
            ".$user['home_phone']."
          </gd:phoneNumber>
          <gd:structuredPostalAddress
              rel='http://schemas.google.com/g/2005#work'
              primary='true'>
            <gd:city>".$user['city']."</gd:city>
            <gd:street>".$user['street']."</gd:street>
            <gd:region>".$user['region']."</gd:region>
            <gd:postcode>".$user['postcode']."</gd:postcode>
            <gd:country>".$user['country']."</gd:country>
            <gd:formattedAddress>
              ".$user['fulladdress']."
            </gd:formattedAddress>
          </gd:structuredPostalAddress>
        </atom:entry>
    ");
}