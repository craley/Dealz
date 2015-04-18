<div id="searchfield" class="panel panel-default">
    <div class="panel-heading">
        <!-- Filter bar -->
        <div class="btn-toolbar" role="group" aria-label="...">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    Condition<span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu" id="queryCond">
                    <li><a href="#">All</a></li>
                    <li><a href="#">New</a></li>
                    <li><a href="#">Used</a></li>
                </ul>
            </div>
            <button type="button" class="btn btn-default">Price</button>

            <input type="hidden" value="" id="currentPage">

            <div class="btn-group" role="group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    Category<span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu" id="queryCategory">
                    <li><a href="#">All</a></li>
                    <li><a href="#">Beauty</a></li>
                    <li><a href="#">Books</a></li>
                    <li><a href="#">Apparel</a></li>
                    <li><a href="#">Automotive</a></li>
                    <li><a href="#">Digital Music</a></li>
                    <li><a href="#">Electronics</a></li>
                    <li><a href="#">Jewelry</a></li>
                    <li><a href="#">Music</a></li>
                    <li><a href="#">Pet Supplies</a></li>
                    <li><a href="#">Shoes</a></li>
                    <li><a href="#">Software</a></li>
                    <li><a href="#">Tools</a></li>
                    <li><a href="#">Toys</a></li>
                    <li><a href="#">Video Games</a></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Search bar -->
    <div class="panel-body">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Keywords..." id="keywords">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" id="queryFire">Go!</button>
            </span>
        </div>

        <!-- Results -->
        <div class="panel panel-primary">
            <div class="panel-heading">Results</div>
            <div class="panel-body" id="searchload">

            </div>
        </div>

    </div>
</div>

<!-- Begin products screen -->
<div id="productfield" class="panel panel-primary hide">
    <div class="panel-heading">Tracked Products</div>
    <div class="panel-body" id="productload">
        <table class="table table-bordered" id="productTable">
            <thead>
                <tr>
                    <th class="col-xs-1"></th>
                    <th class="col-xs-4">Title</th>
                    <th class="col-xs-4">Maker</th>
                    <th class="col-xs-1">ASIN</th>
                    <th class="col-xs-1">Priority</th>
                    <th class="col-xs-1"></th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($products) && !empty($products)): ?>
                    <?php foreach ($products as $value): ?>
                        <tr id="row<?php echo $value['asin']; ?>">
                            <td class="col-xs-1"><button id="offer<?php echo $value['asin']; ?>" class="btn btn-default" type="button">offers</button></td>
                            <td class="col-xs-4"><p><?php echo $value['title']; ?></p></td>
                            <td class="col-xs-2"><p><?php echo $value['maker']; ?></p></td>
                            <td class="col-xs-1"><p><?php echo $value['asin']; ?></p></td>
                            <td class="col-xs-2">
                                <div class="form-group">
                                    <select class="form-control" id="sticky<?php echo $value['asin']; ?>">
                                        <option value="normal" <?php if($value['priority'] == 0) echo "selected='selected'"; ?>>Normal</option>
                                        <option value="email" <?php if($value['priority'] == 1) echo "selected='selected'"; ?>>Email</option>
                                        <option value="text" <?php if($value['priority'] == 2) echo "selected='selected'"; ?>>Text</option>
                                    </select>
                                </div>
                            </td>
                            <td class="col-xs-1"><a href="#" id="remove<?php echo $value['asin']; ?>"><span class="glyphicon glyphicon-trash"></span></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Begin profile screen -->
<div id="profilefield" class="panel panel-default hide" data-uid="<?php echo $uid; ?>">
    <div class="panel-body">
        <form>
            <div class="row">
                <div class="col-xs-6 form-group">
                    <label for="profileFirst">First</label>
                    <input type="text" class="form-control" id="profileFirst"
                           <?php if (isset($profile['firstName']) && !empty($profile['firstName'])) echo "value='{$profile['firstName']}'"; ?>>
                </div>
                <div class="col-xs-6 form-group">
                    <label for="profileLast">Last</label>
                    <input type="text" class="form-control" id="profileLast"
                           <?php if (isset($profile['lastName']) && !empty($profile['lastName'])) echo "value='{$profile['lastName']}'"; ?>>
                </div>
            </div>
            <div class="form-group">
                <label for="profileUsername">Username</label>
                <input type="text" class="form-control" id="profileUsername"
                       <?php if (isset($profile['username']) && !empty($profile['username'])) echo "value='{$profile['username']}'"; ?>>
            </div>
            <div class="form-group">
                <label for="profileEmail">Email</label>
                <input type="email" class="form-control" id="profileEmail"
                       <?php if (isset($profile['email']) && !empty($profile['email'])) echo "value='{$profile['email']}'"; ?>>
            </div>
            <div class="row">
                <div class="col-xs-6 form-group">
                    <label for="profilePhone">Phone</label>
                    <input type="text" class="form-control" id="profilePhone"
                           <?php if (isset($profile['phone']) && !empty($profile['phone'])) echo "value='{$profile['phone']}'"; ?>>
                </div>
                <div class="col-xs-6 form-group">
                    <label for="profileCarrier">Carrier</label>
                    <input type="text" class="form-control" id="profileCarrier"
                           <?php if (isset($profile['carrier']) && !empty($profile['carrier'])) echo "value='{$profile['carrier']}'"; ?>>
                </div>
            </div>
            <div class="checkbox">
                <label><input type="checkbox"> Auto-login</label>
            </div>
            <button type="button" id="updateFire" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>