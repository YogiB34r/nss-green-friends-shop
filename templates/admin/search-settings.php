<?php
/**
 * @var Elastica\Result $result
 */
?>

<h3>Pregled snimljenih upita</h3>

<a href="admin.php?page=gf-search-settings&filter=redirected">Sakrij redirektovane</a>
<table>
    <tr>
        <th>Upit</th>
        <th>Broj ponavljanja</th>
        <th>Preusmeren na</th>
        <th>Akcija</th>
    </tr>
<?php foreach ($term->getTerms() as $result): ?>
<?php
    $url = '<input type="text" style="display:none;" />';
    if ($result->getData()['url'] !== '') {
        $url = '<input readonly class="redirected" type="text" value="'. $result->getData()['url'] .'" />';
    }
?>
    <tr>
        <td><?=$result->getData()['searchQuery']?></td>
        <td><?=$result->getData()['count']?></td>
        <td><?=$url?> <button style="display: none" data-query="<?=$result->getData()['searchQuery']?>" class="redirect">Snimi</button></td>
        <td><a href="#" class="showRedirect">Preusmeri</a></td>
    </tr>
<?php endforeach;?>
</table>

<script>
    jQuery(document).ready(function() {
        jQuery('.redirect').click(function() {
            var parent = jQuery(this).parent();
            var data = {
                term: jQuery(this).data('query'),
                url: parent.find('input').val()
            };
            jQuery.post('/gf-ajax/?saveSearchRedirect=1', data, function(response) {
                if (response == 1) {
                    parent.find('input').prop('readonly', true);
                    parent.find('button').hide();
                    alert('Upit izmenjen.');
                }
            });
        });

        jQuery('.showRedirect').click(function() {
            var inputElement = jQuery(this).parent().prev().find('input');
            if (inputElement.hasClass('redirected')) {
                inputElement.prop('readonly', false);
            } else {
                inputElement.show();
            }
            inputElement.parent().children().show();
        });
    });
</script>