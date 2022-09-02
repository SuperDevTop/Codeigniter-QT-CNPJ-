/*************** TO DO **********************/
"use strict";
$(document).ready(() => {
  $(".contact-list a").on("click", function () {
    $(".contact-list a").removeClass("active");
    $(this).addClass("active");
    $(".contact-item").hide();
    $("." + $(this).data("type")).show(10);

    return false;
  });
  var anchors = $(".contact-list li").click(function () {
    //savesubcat()
    $(this).addClass("active");
    anchors.not(this).removeClass("active");
  });

  $("#all").click(function (e) {
    dataTable.ajax.url("assets/js/data/task.json").load();
    e.preventDefault();
  });
  $("#completed").click(function (e) {
    dataTable.ajax.url("assets/js/data/task-completed.json").load();
    e.preventDefault();
  });
  $("#important").click(function (e) {
    dataTable.ajax.url("assets/js/data/task-important.json").load();
    e.preventDefault();
  });
  $("#trashed").click(function (e) {
    dataTable.ajax.url("assets/js/data/task-trashed.json").load();
    e.preventDefault();
  });

  $('#myForm').validate({
    rules: {
      title: {
        required: true,
        minlength: 3
      },
      date: {
        required: true,
      }
    },
    messages: {
      title: {
        required: "Please provide task title",
        minlength: "Task title must be at least 3 characters"
      },
      date: {
        required: "Please provide date",
      },
    },
    submitHandler: function (form) {
      var rowIndex = $("#taskForm").attr("rowindex");
      const modifiedData = {};
      $.each($("#taskForm .textItemModal"), function () {
        Object.assign(modifiedData, {
          [$(this).attr("data-src")]: $(this).val(),
        });
      });
      $.each($("#taskForm .textAreaModal"), function () {
        Object.assign(modifiedData, {
          [$(this).attr("data-src")]: $(this).val(),
        });
      });

      if ($("input[type='radio'].prio-radio").is(":checked")) {
        var priority = $("input[type='radio'].prio-radio:checked").val();
        Object.assign(modifiedData, {
          priority,
        });
      }

      var status =
        $("input[type='checkbox'].prio-check:checked").val() == "on" ? "1" : "0";
      Object.assign(modifiedData, {
        status,
      });

      if (status == "1") {
        dataTable.row(rowIndex).nodes().to$().addClass("task-selected");
      } else {
        dataTable.row(rowIndex).nodes().to$().removeClass("task-selected");
      }

      if (isEditClick) {
        dataTable.row(rowIndex).data(modifiedData).draw();
      } else {
        dataTable.row.add(modifiedData).draw();
      }
      dataTable.columns.adjust().draw();
      $("#taskForm").modal("toggle");
    }
  });


  //data table initialization
  var rows_selected = [];
  var isEditClick = false;
  const dataTable = $("#taskTbl").DataTable({
    ajax: {
      url: "assets/js/data/task.json",
      dataSrc: function (json) {
        return json.data;
      },
    },
    dom: "rtip",
    columns: [{
        data: "status",
      },
      {
        data: "details",
        render: function (data, type, row) {
          return (
            '<h6 class="task-title">' +
            row.title +
            '</h6><p class="task-date">' +
            row.date +
            '</p><p class="task-details">' +
            row.details +
            "</p>"
          );
        },
      },
      {
        data: "priority",
        orderable: false,
        targets: 0,
        render: function (data, type, full, meta) {
          if (data == "High") {
            return '<div class="col-red" data-toggle="tooltip" title="High"><i class="far fa-arrow-alt-circle-up font-20"></i></i></div>';
          } else if (data == "Medium") {
            return '<div class="col-dark-gray" data-toggle="tooltip" title="Medium"><i class="fas fa-ellipsis-h font-20"></i></div>';
          } else if (data == "Low") {
            return '<div class="col-green" data-toggle="tooltip" title="Low"><i class="far fa-arrow-alt-circle-down font-20"></i></div>';
          }
        },
      },
      {
        data: "action",
        width: 50,
        orderable: false,
        targets: 0,
        render: function (data, type, full, meta) {
          return (
            '<a class="btn-view" data-toggle="tooltip" title="Edit"><i class="far fa-edit m-r-10"></i></a>' +
            '<a class="btn-delete" data-toggle="tooltip" title="Delete"><i class="far fa-trash-alt"></i></a>'
          );
        },
      },
    ],
    createdRow: function (row, data, dataIndex) {
      if (data.status == "1") {
        $(row).addClass("task-selected");
      }
    },
    columnDefs: [{
      targets: 0,
      width: 30,
      searchable: false,
      orderable: false,
      className: "dt-body-center",
      render: function (data, type, full, meta) {
        return (
          '<div class="pretty p-default p-curve p-thick">' +
          '<input type="checkbox"' +
          (data == 1 ? "checked" : "") +
          " />" +
          '<div class="state p-success-o">' +
          "<label></label>" +
          "</div>" +
          "</div>"
        );
      },
    }, ],
    order: [1, "asc"],
  });

  // custom search item
  $("#searchItem").keyup(function () {
    dataTable.search($(this).val()).draw();
  });

  // new task button click
  $("#addBtn").click(function () {
    isEditClick = false;
    $("#taskForm").modal("toggle");
  });

  // select all checkbox click(not in use)
  $("#example-select-all").on("click", function () {
    var rows = dataTable
      .rows({
        search: "applied",
      })
      .nodes();
    $('input[type="checkbox"]', rows).prop("checked", this.checked);
  });

  // checkbox change event
  $("#taskTbl tbody").on("change", 'input[type="checkbox"]', function () {
    if (!this.checked) {
      var el = $("#example-select-all").get(0);
      if (el && el.checked && "indeterminate" in el) {
        el.indeterminate = true;
      }
    }

    var $row = $(this).closest("tr");
    var data = dataTable.row($row).data();
    var rowId = data[0];
    var index = $.inArray(rowId, rows_selected);
    if (this.checked && index === -1) {
      rows_selected.push(rowId);
    } else if (!this.checked && index !== -1) {
      rows_selected.splice(index, 1);
    }
    if (this.checked) {
      $row.addClass("task-selected");
    } else {
      $row.removeClass("task-selected");
    }
  });

  // Handle click on "Delete" button
  $("#taskTbl tbody").on("click", ".btn-delete", function (e) {
    swal({
      title: "Are you sure?",
      text: "Once deleted, you will not be able to recover this contact!",
      buttons: true,
    }).then((willDelete) => {
      if (willDelete) {
        dataTable.row($(this).parents("tr")).remove().draw();
      } else {}
    });
  });

  //edit button click handler
  $("#taskTbl").on("click", ".btn-view", function () {
    isEditClick = true;
    const rowClicked = dataTable.row($(this).closest("tr"));
    $.each($("#taskForm .textItemModal"), function () {
      $(this).val(rowClicked.data()[$(this).attr("data-src")]);
    });
    $.each($("#taskForm .textAreaModal"), function () {
      $(this).val(rowClicked.data()[$(this).attr("data-src")]);
    });
    $("input[name=priority][value=" + rowClicked.data().priority + "]").prop(
      "checked",
      true
    );

    if (rowClicked.data().status == "1") {
      $("#status").prop("checked", true);
    }
    $("#taskForm").attr("rowindex", rowClicked.index());
    $("#taskForm").modal("toggle");
  });




  // clear form when dialog close
  $("#taskForm").on("hidden.bs.modal", function () {
    $(this).find("form").trigger("reset");
  });

  // javascript code for tooltip
  $(document).ajaxComplete(function () {
    // Required for Bootstrap tooltips in DataTables
    $('[data-toggle="tooltip"]').tooltip({
      html: true,
      delay: {
        show: 100,
        hide: 0,
      },
    });
  });
});