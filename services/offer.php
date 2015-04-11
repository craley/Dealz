<div id="offerfield" class="panel panel-primary">
    <div class="panel-heading">Offers for <?php echo "$asin"; ?></div>
    <div class="panel-body" id="offerload">
        <table class="table table-bordered" id="offerTable">
            <thead>
                <tr>
                    <th class="col-xs-4">Vendor</th>
                    <th class="col-xs-4">Condition</th>
                    <th class="col-xs-4">Price</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($data['offers']) and ! empty($data['offers'])): ?>
                    <?php $len = count($data['offers']) ?>
                    <?php for ($x = 0; $x < $len; $x++): ?>
                        <tr>
                            <?php $curr = $data['offers'][$x]; ?>
                            <td class="col-xs-4">
                                <?php echo $curr['vendor']; ?>
                            </td>
                            <td class="col-xs-4">
                                <?php echo $curr['condition']; ?>
                            </td>
                            <td class="col-xs-4">
                                <?php echo $curr['price']; ?>
                            </td>
                        </tr>
                    <?php endfor; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
