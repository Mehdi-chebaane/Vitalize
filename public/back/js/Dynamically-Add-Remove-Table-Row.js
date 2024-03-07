    $('#example tbody').on('click', '.del-row', function () {

        alert("Are You Sure you want to delete this item !! ")
       
    $(this).closest('tr').remove();
})

$(".add-row").click(function() {
    $('#exemple tbody').append('<tr><td><select class="form-select"><optgroup label="This is a group"><option value="12" selected>This is item 1</option><option value="13">This is item 2</option><option value="14">This is item 3</option></optgroup></select></td><td><select class="form-select"><optgroup label="This is a group"><option value="12" selected>This is item 1</option><option value="13">This is item 2</option><option value="14">This is item 3</option></optgroup></select></td><td class="text-center"><a class="del-row" href="javascript:void(0);"><i class="fas fa-trash" style="font-size: 20px;color: rgb(255,0,0);"></i></a></td></tr>')
    });