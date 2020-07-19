<table class="pure-table pure-table-horizontal">
    <thead>
        <tr>
            <th>Email</th>
            <th>Nombre</th>
        </tr>
    </thead>
    <tbody>
        <?php
          foreach($existing as $email => $name) {
        ?>
        <tr>
            <td><?=$email; ?></td>
            <td><?=$name[2]; ?></td>
        </tr>

        <?php
          }
        ?>
    </tbody>
</table>