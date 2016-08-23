// Keys "enum"
var KEY = {
  BACKSPACE: 8,
  ENTER: 13,
  ESCAPE: 27,
  UP: 38,
  DOWN: 40,
  NUMPAD_ENTER: 108,
};

TagTreeBase = function(instance_options) {
  var self = this;

  // Override defaults with instance options.
  self.options = {
    tree_container: null,
    info_container: null,
    tab_container: null,
    filter_bar: null,

    tag_trees: [291],
    default_tree: 291,

    auto_save: false,
    entity_id: 0,
    entity_counts: false,
    entity_type: 'civicrm_contact',
  }
  cj.extend(true, self.options, instance_options);

  // Setup instance attributes
  self.current_tree = null;
  self.container = self.options.tree_container
  self.info_container = self.options.info_container
  self.tab_container = self.options.tab_container
  self.filter_bar = self.options.filter_bar
  return self;
}

TagTreeBase.prototype.load = function() {
    var self = this;

    // Request the data
    cj.ajax({
        url: '/civicrm/ajax/tag/tree',
        data: {
            call_uri: window.location.href,
            entity_type: self.options.entity_type,
            entity_counts: self.options.entity_counts,
            // TODO: This isn't actually supported yet!
            tag_trees: self.options.tag_trees,
        },
        dataType: 'json',
        success: function(data, status, XMLHttpRequest) {
            if(data.code != 1) {
                // TODO: Render error message..
                console.log(data.message);
            }
            else {
                self.setup_trees(data.message);
                if (self.tab_container) {
                  self.setup_tab_panel();
                }
                if (self.info_container) {
                  self.setup_info_panel();
                }
                if (self.filter_bar) {
                  self.setup_tag_filter();
                }
            }
        }
    });
}

TagTreeBase.prototype.setup_trees = function(tree_data) {
    var self = this;

    cj.each(tree_data, function(i, root) {
        // Issue Code and Keyword Trees are built differently
        var tree_id = root.id;

        // Don't process the data if it wasn't requested.
        // TODO: This data shouldn't be returned in the first place...
        if (cj.inArray(parseFloat(tree_id), self.options.tag_trees) == -1) return;

        // All tree types use this basic template, tab controls can be customized by
        // overriding the `add_control_box` method.
        function build_node(tag, parent, depth) {
            var html = '<dt class="lv-'+depth+' '+markReserved(tag)+'" id="'+tagLabel(tag.id)+'" description=\''+tag.description+'\' tLvl="'+depth+'" tid="'+tag.id+'" parent="'+parent.id+'">'+
            '<div class="tag-row"><div class="ddControl '+markChildren(tag)+'"></div>'+
            self.add_control_box(tag, tree_id) +
            '<div class="tag"><span class="name">'+tag.name+'</span></div>'+
            '<span class="entityCount" style="display:none">('+tag.entity_count+')</span>' +
            '</div></dt>';

            html += '<dl class="lv-'+depth+'" id="'+tagLabel(tag.id)+'" tLvl="'+depth+'" >';
            cj.each(tag.children, function(i, child) {
                html += build_node(child, tag, depth+1);
            });
            html += "</dl>";
            return html;
        }

        // Create a new DOM tree, insert into the TagTree.container, and animate it
        var tree = cj('<div class="BBTree '+self.type+'" id="tagtree_'+tree_id+'" data-tree-id="'+tree_id+'">'+build_node(root, root, 0)+'</div>');
        if (tree_id != self.options.default_tree) {
            tree.addClass('hidden');
        }
        else {
            self.current_tree = tree;
        }
        console.log("Finished with tree_id: "+tree_id);
        self.container.append(tree);
        120   - self.container.addClass('TreeWrap');
        self.animate_tree(tree);
    });
    // Hook for customization by child classes
    self.customize_tree();
}

TagTreeBase.prototype.add_control_box = function(tag, tree_id) {
    return ''; // Default implementation with no controls
}

TagTreeBase.prototype.animate_tree = function(tree) {
    var self = this;
    tree.find('dl').hide()
    tree.find('dl.lv-0').show();
    tree.delegate('dt .treeButton', 'click', function() {
        var tagNode = cj(this).parent().parent();
        var tagList = tagNode.next('dl');
        if (tagList.hasClass('open')) {
            tagNode.find('div').removeClass('open');
            tagList.removeClass('open').hide();
        }
        else {
            tagNode.find('div').addClass('open');
            tagList.addClass('open').show();
        }
    });
}

TagTreeBase.prototype.customize_tree = function() {
    return; // Default implementation with no customization
}

TagTreeBase.prototype.setup_tab_panel = function() {
    var self = this;
    if (self.tab_container) {
        var tab_list = cj('<ul></ul>');
        cj.each(self.options.tag_trees, function(i, tree_id) {
            var tab_name = cj('dt#'+tagLabel(tree_id)+' span.name').html();
            var tab_is_active = (tree_id == self.options.default_tree ? 'active' : '')
            var tab_item = cj('<li class="tab '+tab_is_active+'" data-bbtree-id="'+tree_id+'">'+tab_name+'</li>');
            tab_item.click(function() {
                var button = cj(this);
                var tree_id = 'tagtree_'+button.attr('data-bbtree-id')

                self.current_tree.hide();
                cj('#'+tree_id).show();

                self.tab_container.find('li').removeClass('active');
                button.addTag('active');
            });
        tab_list.append(tab_item);
        });
        self.tab_container.html(tab_list);
    }
}

TagTreeBase.prototype.setup_info_panel = function() {
    var self = this;
    if (self.info_container) {
        self.container.delegate('dt', 'hover',
        function() {
            var dt_id = cj(this).attr('id');
            if(dt_id != 'tagLabel_291' && dt_id != 'tagLabel_296' ) {
                var tagCount = ' ';
                tagCount += cj('span.entityCount', this).html().match(/[0-9]+/);
                if(tagCount == ' ' +null) {
                    tagCount = cj('span.entityCount', this).html();
                }
            }
            var tagName = cj('div.tag span', this).html();
            var tagId = cj(this).attr('tid');
            var isReserved = cj(this).hasClass('isReserved') == true ? 'True' : 'False';
            var tagDescription = cj(this).attr('description');
            if(tagDescription == 'null') {
                tagDescription = '';
            }
            self.info_container.find('.tagName span')
            self.info_container.find('.tagName span').html(tagName);
            self.info_container.find('.tagId span').html(tagId);
            self.info_container.find('.tagDescription span').html(tagDescription);
            self.info_container.find('.tagReserved span').html(isReserved);
            self.info_container.find('.tagCount span').html(tagCount);
            },
            function() {
                self.info_container.find('.tagName span').html('');
                self.info_container.find('.tagID span').html('');
                self.info_container.find('.tagDescription span').html('');
                self.info_container.find('.tagReserved span').html('');
                self.info_container.find('.tagCount span').html('');
            }
        );
    }
}

TagTreeBase.prototype.setup_tag_filter = function() {
    var self = this;
    self.selected_tag = null;
    self.matched_tags = null;
    self.filter_timeout_id = null;
    self.clear_button = cj('<div id="issue-code-clear" >x</div>');
    self.empty_panel = cj('<div id="issue-code-empty" >No Results Found</div>');
    self.wait_panel = cj('<div id="issue-code-wait"></div>');
    self.filter_bar.addClass('tag_filter_bar');
    self.container.prepend(self.clear_button, self.empty_panel);
    self.container.prepend(self.wait_panel)
    self.wait_panel.hide();

    self.clear_button.click(function() {
        self.reset_filter();
    });

    // We bind to keydown here so that default behaviors can be prevented
    // and we have access to non-printable keystrokes. We suppport ESC for
    // "reset", ENTER/TAB for "toggle tag", UP/DOWN for tag selection, and will
    // trigger a tag search when you use the BACKSPACE KEY.
    self.filter_bar.keydown(function(event) {
        if (event.which == KEY.ESCAPE) {
            self.reset_filter();
        }
        else if (event.which == KEY.BACKSPACE) {
            clearTimeout(self.filter_timeout_id);
            self.filter_timeout_id = setTimeout(function() {
                if (self.filter_bar.val().length < 3) {
                    self.wait_panel.fadeIn("fast", self.filter_tags.bind(self));
                }
                else {
                    self.filter_tags();
                }
            }, 300);
            self.filter_bar.focus();
        }
        else if (self.selected_tag) {
            var cur_index = self.matched_tags.index(self.selected_tag);
            if (event.which == KEY.UP) {
                event.preventDefault();
                if (cur_index != 0) {
                    self.select_tag(cj(self.matched_tags[cur_index-1]));
                }
                self.filter_bar.focus();
            }
            else if (event.which == KEY.DOWN) {
                event.preventDefault();
                if (cur_index != self.matched_tags.length-1) {
                    self.select_tag(cj(self.matched_tags[cur_index+1]));
                }
                self.filter_bar.focus();
            }
            else if (event.which == KEY.ENTER || event.which == KEY.NUMPAD_ENTER) {
                event.preventDefault();
                event.stopImmediatePropagation();
                self.selected_tag.find('input[type="checkbox"]').click();
            }
            else if (event.which == KEY.TAB) {
                event.preventDefault();
                self.selected_tag.find('input[type="checkbox"]').click();
            }
        }
    });

    // Only trigger the search when printable keys are entered or the BACKSPACE
    // key is used (see above). The search should start 300ms after the last
    // action so always start by cancelling the current timeout function.
    self.filter_bar.keyup(function(event) {
        if (event.which > 40 || event.which === 32) {
            clearTimeout(self.filter_timeout_id);
            self.filter_timeout_id = setTimeout(function() {
                if (self.filter_bar.val().length < 3) {
                    self.wait_panel.fadeIn("fast", self.filter_tags.bind(self));
                }
                else {
                    self.filter_tags();
                }
            }, 300);
        }
    });
 }

TagTreeBase.prototype.get_tags = function() {
    var self = this;
    return self.current_tree.find('dt').not('.lv-0');
}

TagTreeBase.prototype.reset_filter = function() {
    var self = this;
    self.selected_tag = null;
    self.matched_tags = null;
    clearTimeout(self.filter_timeout_id);
    self.filter_timeout_id = null;
    self.filter_bar.val('');
    self.current_tree.find('.ddControl.open').click();
    self.get_tags().removeClass('search-hidden search-match search-parent search-highlighted');
    self.clear_button.fadeOut("fast");
    self.empty_panel.fadeOut("fast");
    self.wait_panel.hide();
}

// An empty search bar resets the filter. Anything else triggers
// a search through the whole tag container for matching tags.
TagTreeBase.prototype.filter_tags = function() {
    var self = this;
    var search_term = self.filter_bar.val().toLowerCase();
    if (search_term.length == 0) {
        self.reset_filter();
    }
    else {
        function highlightParent() {
        var parent = cj(this).parent();
            if (!parent.hasClass('lv-0')) {
                parent.prev('dt').addClass('search-parent');
                highlightParent.call(parent);
            }
        }

        var has_matches = false;
        var tags = self.get_tags();
        tags.removeClass('search-hidden search-match search-parent search-highlighted');
        tags.each(function() {
            var tag = cj(this);
            if(tag.find('span.name').text().toLowerCase().indexOf(search_term) > -1) {
                has_matches = true;
                tag.addClass('search-match');
            }
        });
        self.clear_button.fadeIn( "slow" );

        if (has_matches) {
            self.empty_panel.fadeOut("fast");
            self.matched_tags = cj(".search-match");
            self.matched_tags.each(highlightParent);
            tags.not(self.matched_tags).not('.search-parent').addClass('search-hidden');
            self.current_tree.find("dt.search-parent .ddControl").not(".open").addClass('open').parent().parent().next('dl').addClass('open').show(); // .click();

            // This has to happen after the lists are opened/hidden
            self.select_tag(self.matched_tags.first());
        }
        else {
            self.empty_panel.fadeIn("fast");
            self.matched_tags = null;
            self.selected_tag = null;
            tags.addClass('search-hidden');
        }
        self.wait_panel.hide();
    }
}

// Deselects the currently selected tag and selects the provided
// tag. Makes sure that the provided tag is visible if a scroll
// bar is active.
TagTreeBase.prototype.select_tag = function(tag) {
    var self = this;
    if (self.selected_tag) {
        self.selected_tag.removeClass("search-highlighted");
        self.selected_tag = null;
    }

    // Set the new selected tag up
    tag.addClass("search-highlighted");
    self.selected_tag = tag;

    // Make sure that the newly selected tag is visible
    var tag_rect = tag[0].getBoundingClientRect();
    var container_rect = self.container[0].getBoundingClientRect();
    if (tag_rect.top < container_rect.top) {
        self.container.scrollTop(self.container.scrollTop() + tag_rect.top - container_rect.top);
    }
    else if (tag_rect.bottom > container_rect.bottom) {
        self.container.scrollTop(self.container.scrollTop() + tag_rect.bottom - container_rect.bottom);
    }
}

TagTreeBase.prototype.notify = function(type, title, message) {
    var self = this;
    var timeout = 2000;
    var type_class = '';
    switch(type) {
        case 'Unauthorized':
        case 'Error':
            type_class = 'error';
            title = 'Error - '+title;
            timeout = 10000; break;
        case 'Success':
            type_class = 'success';
            title = 'Success - '+title;
            timeout = 4000; break;
        default:
            type_class = 'info';
            title = 'Info - '+title;
            timeout = 5000; break;
    }
    console.log('['+title+'] '+message);
    CRM.alert(message, title, type_class, {expires:timeout});
}











TagTreeTag = function(instance_options) {
    var self = this;
    self.type = "tagging";
    TagTreeBase.call(self, instance_options);
    return self;
}
TagTreeTag.prototype = new TagTreeBase();

TagTreeTag.prototype.add_control_box = function(tag) {
    if(tag.id == 291 || tag.id == 296) {
        return ''; // No controls added to root elements;
    }
    else {
        //NOTE: name="tag[###]" allows save on submit functionality to work
        return '<span class="fCB"><ul><li><input type="checkbox" tID="'+tag.id+'" name="tag['+tag.id+']" class="checkbox '+markChecked(tag)+'" '+markChecked(tag)+'></input></li></ul></span>';
     }
}

TagTreeTag.prototype.animate_tree = function(tree) {
    var self = this;
    TagTreeBase.prototype.animate_tree.call(self, tree);

    tree.find('input[type="checkbox"]').change(function(event) {
        var checkbox = cj(this);
        var tag_id = checkbox.attr("tID");
        var tag_node = tree.find('dt#'+tagLabel(tag_id));
        var tag_name = tag_node.find('.name').text();

        // If we are currently checked then delete, otherwise create a new entity_tag.
        if (checkbox.is(":checked")) {
            console.log("Adding checkmark!");
            self.addTagCheck(tag_id);
            if(self.options.auto_save && self.options.entity_id) {
                cj.ajax({
                    url: '/civicrm/ajax/entity_tag/create',
                    data: {
                        entity_type: self.options.entity_type,
                        entity_id: self.options.entity_id,
                        call_uri: window.location.href,
                        tag_id: tag_id
                    },
                    dataType: 'json',
                    success: function(data, status, XMLHttpRequest) {
                        if(data.code == 0) {
                            console.log("Failed to create entity_tag: ["+self.options.entity_id+"#"+tag_id+"]");
                            self.notify('Error', 'Add Tag', '<span>'+tag_name+'</span> was unable to be add to this entity.');
                            self.removeTagCheck(tag_id);
                        }
                        else if (data.code == 2) {
                            console.log("You do not have the required permissions to add ["+self.options.entity_id+"#"+tag_id+"]");
                            self.notify('Error', 'Add Tag', 'You do not have the required permissions to add tags to this record.');
                            self.removeTagCheck(tag_id);
                        }
                        else {
                            console.log("Successfully created entity_tag: ["+self.options.entity_id+"#"+tag_id+"]");
                            self.notify('Success', 'Add Tag', '<span>'+tag_name+'</span> was added to this entity.');
                        }
                    }
                });
            }
        }
        else {
            console.log("Removing checkmark!");
            self.removeTagCheck(tag_id);
            if(self.options.auto_save && self.options.entity_id) {
                cj.ajax({
                    url: '/civicrm/ajax/entity_tag/delete',
                    data: {
                        entity_type: self.options.entity_type,
                        entity_id: self.options.entity_id,
                        call_uri: window.location.href,
                        tag_id: tag_id
                    },
                    dataType: 'json',
                    success: function(data, status, XMLHttpRequest) {
                        if(data.code == 0) {
                            self.notify('Error', 'Remove Tag', '<span>'+tag_name+'</span> was unable to be removed from this entity.');
                            self.addTagCheck(tag_id);
                        }
                        else if (data.code == 2) {
                            self.notify('Error', 'Remove Tag', 'You do not have the required permissions to remove tags from this record.');
                            self.addTagCheck(tag_id);
                        }
                        else {
                            self.notify('Success', 'Remove Tag', '<span>'+tag_name+'</span> was removed from this entity.');
                        }
                    }
                });
            }
        }
    })
}

TagTreeTag.prototype.removeTagCheck = function(tag_id) {
    var self = this;
    var tag = self.container.find('dt#'+tagLabel(tag_id));

    // Uncheck the box and remove class associations for the tag and its parents
    console.log("Removing check from");
    console.log(tag.find('input[type="checkbox"]'));
    tag.removeClass('checked subChecked');
    tag.find('input[type="checkbox"]').attr('checked', false);
    if(self.container.find('dl#'+tagLabel(tag_id)+' dt.checked').length > 0) {
        // decendants are checked, then switch to subChecked status.
        tag.addClass('subChecked');
    }
    else if(tag.siblings('dt.checked, dt.subChecked').length == 0) {
        // none of our siblings are checked or subChecked, remove from parents as well.
        // Parents are run in order going up the tree. If any parent is checked/subChecked
        // return false and stop the traversal.
        cj.each(tag.parents('dl').not('.lv-0'), function(i, parent_list) {
            var parent_label = cj(parent_list).attr('id');
            var parent = self.container.find('dt#'+parent_label);
            parent.removeClass('subChecked');
            if(parent.hasClass('checked')) {
                return false;
            }
            else {
                parent.removeClass('subChecked');
                if(parent.siblings('dt.checked, dt.subChecked').length > 0 ) {
                    return false;
                }
            }
        });
    }

    // Remove the name from the tag list at the top (if it exists)
    var tag_list = cj('.contactTagsList.help span');
    if (tag_list.length) {
        var tag_name = tag.find('.name').html();
        var tag_list_items = tag_list.html().split(" • ");
        // This is done in a crappy way to make all browsers happy
        cj.each(tag_list_items, function(i, item) {
            if (tag_name == item) {
                tag_list_items.splice(i, 1);
            }
        });
        tag_list.html(tag_list_items.join(" • "));
    }

    // Decrement the number in the tagging tab
    var tab_counter = cj('li#tab_tag em');
    if (tab_counter.length) {
        tab_counter.html(parseFloat(tab_counter.html())-1);
    }
}

TagTreeTag.prototype.addTagCheck = function(tag_id) {
    var self = this;
    var tag = self.container.find('dt#'+tagLabel(tag_id));

    // Check the box and add class associations for the tag and its parents
    tag.addClass('checked');
    tag.find('input[type="checkbox"]').attr('checked', true);
    tag.parents('dl').not('.lv-0').prev('dt').addClass('subChecked');

    // Add the name to the tag list at the top (if it exists)
    var tag_list = cj('.contactTagsList.help span');
    if (tag_list.length) {
        var tag_name = tag.find('.name').html();
        var tag_list_items = tag_list.html().split(" • ");
        tag_list_items.push(tag_name);
        tag_list_items.sort();
        tag_list.html(tag_list_items.join(" • "));
    }

    // Increment the number in the tagging tab (if it exists)
    var tab_counter = cj('li#tab_tag em');
    if (tab_counter.length) {
        tab_counter.html(parseFloat(tab_counter.html())+1);
    }
 }

TagTreeTag.prototype.customize_tree = function() {
    var self = this;
    if (self.options.entity_id) {
        cj.ajax({
            url: '/civicrm/ajax/entity_tag/get',
            data: {
                entity_type: self.options.entity_type,
                entity_id: self.options.entity_id,
                call_uri: window.location.href,
            },
            dataType: 'json',
            success: function(data, status, XMLHttpRequest) {
                if(data.code != 1 ) {
                    console.log(data.message);
                }
                else {
                    cj.each(data.message, function(i, tag_id){
                        var tagNode = self.container.find(' dt#'+tagLabel(tag_id));
                        tagNode.find('.checkbox').attr('checked','true').addClass('checked');
                        tagNode.addClass('checked');
                        tagNode.parents('dl').not('.lv-0').prev('dt').addClass('subChecked');
                    });
                }
            }

        });
    }
}








function TagTreeManage(instance_options) {
    var self = this;
    self.type = "edit"
    TagTreeBase.call(self, instance_options);
    self.dialog = cj('<div id="BBDialog"></div>');
    self.container.append(self.dialog);
    self.dialog_defaults = {
        show: false,
        closeOnEscape: true,
        draggable: true,
        height: 300,
        width: 300,
        modal: true,
        resizable: true,
        bgiframe: true, // Requires bgiframe plugin...do we have that?
        close: function() {
            self.dialog.html('');
            self.dialog.dialog("destroy");
        },
    }
    return self;
}
TagTreeManage.prototype = new TagTreeBase();

TagTreeManage.prototype.add_control_box = function(tag, tree_id) {
    if((tag.id == 291 || tag.id == 296) && this.type != 'modal') {
        return '<span class="fCB"><ul>'+
        '<li class="printTag"></li>'+
        '<li class="addTag" title="Add New Tag" do="add"></li>'+
        '</ul></span>';
    }
    else if (tree_id == 291) {
        return '<span class="fCB"><ul>'+
        '<li class="addTag" title="Add New Tag" do="add"></li>'+
        '<li class="removeTag" title="Remove Tag" do="remove"></li>'+
        '<li class="moveTag" title="Move Tag" do="move"></li>'+
        '<li class="updateTag" title="Update Tag" do="update"></li>'+
        '<li class="mergeTag" title="Merge Tag" do="merge"></li>'+
        '</ul></span>'
    }
    else if (tree_id == 296) {
        return '<span class="fCB"><ul>'+
        '<li class="removeTag" title="Remove Keyword" do="remove"></li>'+
        '<li class="updateTag" title="Update Keyword" do="update"></li>'+
        '<li class="mergeTag" title="Merge Keyword" do="mergeKW"></li>'+
        '<li class="convertTag" title="Convert Keyword" do="convert"></li>'+
        '</ul></span>';
    }
}


TagTreeManage.prototype.animate_tree = function(tree) {
    var self = this;
    TagTreeBase.prototype.animate_tree.call(self, tree);

    // if it's reserved, there should be no ability to edit it, unless you're updating it.
    tree.find('.printTag').click(function() {
        var mywindow = window.open('', 'PrintTags');
        mywindow.document.body.innerHTML="";
        mywindow.document.write('<!DOCTYPE html><html><head><title>Print Tags</title>');
        mywindow.document.write('<link type="text/css" rel="stylesheet" href="/sites/default/themes/Bluebird/css/tags/tags.css" />');
        mywindow.document.write('</head><body class="popup">');
        mywindow.document.write('<div class="BBTree" style="height:auto;width:auto;overflow-y:hidden;">');
        mywindow.document.write(tree.html());
        mywindow.document.write('</div>');
        mywindow.document.write('</body></html>');
        mywindow.print();
    });

    tree.delegate('.addTag', 'click', function() {
        var tag = cj(this).closest('dt')
        self.addTagModal(tag);
    });

    tree.delegate('.removeTag', 'click', function() {
        var tag = cj(this).closest('dt');
        var tag_name = tag.find('.name').text();
        if (tag.hasClass('isReserved')) {
            alert(tag_name+' is reserved and cannot be removed. Use update to remove reserved status.');
        }
        else if (tag.next('dl').children().length != 0) {
            alert(tag_name+' has child tags and cannot be removed. Make sure any child tags are removed first.');
        }
        else {
            self.removeTagModal(tag);
        }
    });

    tree.delegate('.updateTag', 'click', function() {
        var tag = cj(this).closest('dt')
        self.updateTagModal(tag);
    });

    tree.delegate('.moveTag', 'click', function() {
        var tag = cj(this).closest('dt');
        var tag_name = tag.find('.name').text();
        if (tag.hasClass('isReserved')) {
            alert(tag_name+' is reserved and cannot be moved. Use update to remove reserved status.');
        }
        else if (tag.next('dl').children().length != 0) {
            alert(tag_name+' has child tags and cannot be moved.');
        }
        else {
            self.moveTagModal(tag);
        }
    });

    tree.delegate('.mergeTag', 'click', function() {
        var tag = cj(this).closest('dt');
        var tag_name = tag.find('.name').text();
        if (tag.hasClass('isReserved')) {
            alert(tag_name+' is reserved and cannot be merged. Use update to remove reserved status.');
        }
        else if (tag.next('dl').children().length != 0) {
            alert(tag_name+' has child tags and cannot be merged.');
        }
        else {
            self.mergeTagModal(tag);
        }
    });

    tree.delegate('.convertTag', 'click', function() {
        var tag = cj(this).closest('dt');
        var tag_name = tag.find('.name').text();
        if (tag.hasClass('isReserved')) {
            alert(tag_name+' is reserved and cannot be converted. Use update to remove reserved status.');
        }
        else {
            self.convertKeywordModal(tag);
        }
    });

}

TagTreeManage.prototype.addTagModal = function(parent) {
    var self = this;
    var parent_name = parent.find('.name').html();

    self.dialog.html(
        '<div class="modalHeader">Add new tag under ' + parent_name + '</div>' +
        '<div class="modalInputs">' +
        '<div><span>Tag Name:</span ><input type="text" name="tagName" /></div>' +
        '<div><span>Description:</span><input type="text" name="tagDescription" /></div>' +
        '<div><span>Reserved:</span><input type="checkbox" name="isReserved"/></div>' +
        '</div>'
    );

    var settings = cj.extend(self.dialog_defaults, {
        title: 'Add New Tag',
        open: function() {
            self.dialog.find('input[name=tagName]').focus();
        },
        buttons: [{
            text: 'Add',
            click: function() {
                var parent_id = parent.attr('tid');
                var tag_name = cj.trim(self.dialog.find('input[name=tagName]').val());
                var tag_desc = cj.trim(self.dialog.find('input[name=tagDescription]').val())
                var tag_resv = self.dialog.find('input:checked[name=isReserved]').length;
                if (tag_name.length == 0) {
                    alert("Tag must have a valid name.");
                    return;
                }

                cj.ajax({
                    url: '/civicrm/ajax/tag/create',
                    data: {
                        name: tag_name,
                        description: tag_desc,
                        parent_id: parent_id,
                        is_reserved: tag_resv,
                        call_uri: window.location.href
                    },
                    dataType: 'json',
                    success: function(data, status, xhr) {
                        console.log(data);
                        if (data.code != 1) {
                            if (data.message == 'DB Error: already exists') {
                                self.notify('Error', 'Add Tag', 'Tag <span>'+tag_name+'</span> already exists.')
                            }
                            else {
                                self.notify('Error', 'Add Tag', 'Tag <span>'+tag_name+'</span> was unable to be added.')
                            }
                        }
                        else {
                            tag = data.message;
                            depth = parseFloat(parent.attr('tLvl'))+1;
                            parent.next('dl').prepend(
                                '<dt class="lv-'+depth+' '+markReserved(tag)+'" id="'+tagLabel(tag.id)+'" description=\''+tag.description+'\' tLvl="'+depth+'" tid="'+tag.id+'" parent="'+parent_id+'">'+
                                '<div class="tag-row"><div class="ddControl"></div>'+
                                self.add_control_box(tag, self.current_tree.attr('data-tree-id')) +
                                '<div class="tag"><span class="name">'+tag.name+'</span></div>'+
                                '<span class="entityCount" style="display:none">(0)</span>' +
                                '</div></dt>' +
                                '<dl class="lv-'+depth+'" id="'+tagLabel(tag.id)+'" tLvl="'+depth+'" ></dl>'
                            );
                            parent.find('.ddControl').addClass('treeButton').click();

                            self.notify('Success', 'Add Tag',
                                'Tag <span>'+tag_name+'</span> was added successfully under <span>'+parent_name+'</span>. '+
                                'It\'s new description is <span>'+tag_desc.replace(/</g,"&lt;").replace(/>/g,"&gt;")+'</span>. '+
                                'It is <span> '+(tag_resv ? '' : 'not ')+' reserved</span>'
                            );
                        }
                        self.dialog.dialog("close");
                    }
                });
            }
        }, {
            text: "Cancel",
            click: function() {
                cj(this).dialog("close");
            }
        }]
    });

    self.dialog.dialog(settings).dialog("open");
}

TagTreeManage.prototype.removeTag = function(tag) {
    var parent_list = tag.parent();
    var parent_tag = parent_listprev('dt');
    tag.next('dl').remove();
    if (tag.siblings().length == 0) {
        parent_list.removeClass('open');
        parent_tag.find('.ddControl').removeClass('treeButton open');
    }
    tag.remove();
}

TagTreeManage.prototype.moveTag = function(tag, parent) {

}

TagTreeManage.prototype.removeTagModal = function(tag) {
    var self = this;
    var tag_id = tag.attr('tid');
    var tag_name = tag.find('.name').html();
    var parent_tag = tag.parent().prev('dt');
    var parent_name = parent_tag.find('.name').html();

    self.dialog.html(
        '<div class="modalHeader">Remove Tag: <span>' + tag_name + '</span></div>'
    );

    var settings = cj.extend(self.dialog_defaults, {
        title: 'Remove Tag',
        buttons: [{
            text: "Remove",
            click: function() {
                cj.ajax({
                    url: '/civicrm/ajax/tag/delete',
                    data: {
                        id: tag_id,
                        call_uri: window.location.href
                    },
                    dataType: 'json',
                    success: function(data, status, XMLHttpRequest) {
                        if(data.code != 1) {
                            self.notify('Error', 'Remove Tag', 'Tag <span>'+tag_name+'</span> was unable to be removed.')
                        }
                        else {
                            self.notify('Success', 'Remove Tag', 'Tag <span>'+tag_name+'</span> was removed under <span>'+parent_name+'</span>.');
                            self.removeTag(tag)
                        }
                        self.dialog.dialog("close");
                    }
                });
            }
        }, {
            text: "Cancel",
            click: function() {
                cj(this).dialog("close");
            }
        }]
    });

    self.dialog.dialog(settings).dialog("open");
}

 TagTreeManage.prototype.updateTagModal = function(tag) {
     var self = this;
     var tag_id = tag.attr('tid');
     var tag_name = tag.find('.name').html();
     var tag_desc = tag.attr('description').replace('/"/', '\"'); // Wy?
     var tag_resv = tag.hasClass('isReserved');

     console.log(self.dialog);
     self.dialog.html(
         '<div class="modalHeader">Update Tag <span>' + tag_name + '</span></div>' +
         '<div class="modalInputs">' +
         '<div><span>Tag Name:</span><input type="text" name="tagName" value="'+tag_name+'" /></div>' +
         '<div><span>Description:</span ><input type="text" name="tagDescription" value="'+tag_desc+'"/></div>' +
         '<div><span>Reserved:</span><input type="checkbox" name="isReserved" '+(tag_resv ? 'checked' : '')+"/></div>" +
         '</div>'
     );

    var settings = cj.extend(self.dialog_defaults, {
        title: 'Update Tag',
        buttons: [{
            text: "Update",
            click: function() {
                var new_name = cj.trim(self.dialog.find('input[name=tagName]').val());
                var new_desc = cj.trim(self.dialog.find('input[name=tagDescription]').val());
                var new_resv = self.dialog.find('input:checked[name=isReserved]').length;
                if (new_name.length == 0) {
                    alert("Tag must have a valid name.");
                    return;
                }
                else {
                    cj.ajax({
                        url: '/civicrm/ajax/tag/update',
                        data: {
                            name: new_name,
                            description: new_desc,
                            id: tag_id,
                            is_reserved: new_resv,
                            call_uri: window.location.href
                        },
                        dataType: 'json',
                        success: function(data, status, XMLHttpRequest) {
                            if(data.code != 1) {
                                BBTree.reportAction(['updat',0,tagUpdate, data.message]);
                                if (data.message == 'DB Error: already exists') {
                                    self.notify('Error', 'Update Tag', 'Tag <span>'+new_name+'</span> already exists')
                                }
                                else {
                                    self.notify('Error', 'Update Tag', 'Tag <span>'+tag_name+'</span> was unable to be updated')
                                }
                            }
                            else {
                                var tag_data = data.message;
                                if(parseFloat(tag_data.is_reserved)) {
                                    tag.removeClass('isReserved');
                                }
                                else{
                                    tag.addClass('isReserved');
                                }
                                tag.find('.name').html(tag_data.name);
                                tag.attr('description', tag_data.description);

                                var msg = 'Tag <span>'+tag_name+'</span> was updated.';
                                if (tag_name != new_name) {
                                    msg += ' Its new name is <span>'+new_name+'</span>.';
                                }
                                if (tag_desc != new_desc) {
                                    msg += ' Its new description is <span>'+new_desc+'</span>. ';
                                }
                                if (tag_resv != new_resv) {
                                    msg += ' It is now '+(new_resv ? 'reserved.' : 'unreserved.')
                                }
                                self.notify('Success', 'Update Tag', msg);
                            }
                            self.dialog.dialog("close");
                        }
                    });
                }
            }
            }, {
                text: "Cancel",
                click: function() {
                cj(this).dialog("close");
            }
        }]
    });

    self.dialog.dialog(settings).dialog("open");
}


TagTreeManage.prototype.moveTagModal = function(tag) {
    var self = this;
    var tag_id = tag.attr('tid');
    var tag_name = tag.find('.name').html();

    self.dialog.html(
        '<div class="modalHeader">Move <span>' + tag_name + ' under Tag...</span></div>' +
        '<div class="BBTree modal">' +
        self.current_tree.html() +
        '</div>'
    );
    self.dialog.find('#'+tagLabel(tag_id)).hide();
    self.dialog.find('span.fCB').empty().html('<input type="radio" class="selectRadio" name="selectTag"/>');


    var settings = cj.extend(self.dialog_defaults, {
        title: 'Move Tag',
        height: 500,
        width: 600,
        open: function() {
            // can't run this until dialog is shown
            self.animate_tree(self.dialog.find('.BBTree'));
        },
        buttons: [{
            text: "Move",
            click: function() {
                cj.ajax({
                    success: function() {
                        if (data.code != 1) {
                            self.notify('Error', 'Move Tag', 'Tag <span>'+tag_name+'</span> was unable to be moved under <span>'+dest_name+'</span>.');
                        return;
                        }
                        else {

                            self.notify('Success', 'Move Tag', 'Tag <span>'+tag_name+'</span> was moved under <span>'+dest_name+'</span>.');
                        }
                    }
                })
            }
        }, {
            text: "Cancel",
            click: function() {
                cj(this).dialog("close");
            }
        }]
    });

    self.dialog.dialog(settings).dialog("open");
}

TagTreeManage.prototype.mergeTagModal = function(tag) {
    var self = this;
    var tag_id = tag.attr('tid');
    var tag_name = tag.find('.name').html();

    self.dialog.html(
        '<div class="modalHeader">Merge <span>' + tag_name + '</span> into Selected Tag... (note: this is a slow process)</div>' +
        '<div class="modal">' +
        self.current_tree.html() +
        '</div>'
    );
    self.dialog.find('#'+tagLabel(tag_id)).hide();
    self.dialog.find('span.fCB').empty().html('<input type="radio" class="selectRadio" name="selectTag"/>');

    var settings = cj.extend(self.dialog_defaults, {
        title: 'Merge Tag',
        height: 500,
        width: 600,
        open: function() {
            // can't run this until dialog is shown
            self.animate_tree(self.dialog.find('.BBTree'));
        },
        buttons: [{
            text: "Merge",
            click: function() {
                cj.ajax({
                    success: function() {
                        if (data.code != 1) {
                            self.notify('Error', 'Merge Tag', 'Tag <span>'+tag_name+'</span> was unable to be merged into <span>'+dest_name+'</span>');
                            return;
                        }
                        else {
                            self.notify('Success', 'Merge Tag', 'Tag <span>'+tag_name+'</span> was merged into <span>'+dest_name+'</span>.');
                        }
                    }
                })
            }
        }, {
            text: "Cancel",
            click: function() {
                cj(this).dialog("close");
            }
        }]
    });

    self.dialog.dialog(settings).dialog("open");
}

TagTreeManage.prototype.convertKeywordModal = function(tag) {
    var self = this;
    var tag_id = tag.attr('tid');
    var tag_name = tag.find('.name').html();

    self.dialog.html(
        '<div class="modalHeader">Convert <span>' + tag_name + '</span> into a Issue Code under...</div>' +
        '<div class="BBTree modal">' +
        self.container.find('#tagtree_291').html() +
        '</div>'
    );
    self.dialog.find('#'+tagLabel(tag_id)).hide();
    self.dialog.find('span.fCB').empty().html('<input type="radio" class="selectRadio" name="selectTag"/>');

    var settings = cj.extend(self.dialog_defaults, {
        title: 'Convert Keyword to Issue Code',
        height: 500,
        width: 600,
        open: function() {
            // can't run this until dialog is shown
            self.animate_tree(self.dialog.find('.BBTree'));
        },
        buttons: [{
            text: "Convert",
            click: function() {
                cj.ajax({
                    success: function() {
                        if (data.code != 1) {
                            if (data.message == 'DB Error: already exists') {
                                self.notify('Error', 'Convert Keyword', 'Keyword <span>'+tagname+'</span> was unable to be converted because Issue Code <span>'+dest_name+'</span> already exists');
                            }
                            else {
                                self.notify('Error', 'Convert Keyword', 'Keyword <span>'+tag_name+'</span> was unable to be converted to an Issue Code under <span>'+dest_name+'</span>');
                            }
                            return;
                        }
                        else {
                            self.notify('Success', 'Convert Keyword', 'Keyword <span>'+tag_name+'</span> was converted into an Issue Code under <span>'+dest_name+'</span>.');
                        }
                    }
                })
            }
            }, {
            text: "Cancel",
            click: function() {
                cj(this).dialog("close");
            }
        }]
    });

    self.dialog.dialog(settings).dialog("open");
}


function markChecked(tag) {
    return tag.is_checked == 'checked' ? 'checked' : '';
}
function markReserved(tag) {
    return parseFloat(tag.is_reserved) ? 'isReserved' : '';
}
function markChildren(tag) {
    return tag.children.length > 0 ? 'treeButton' : '';
}
function tagLabel(tag_id) {
    return 'tagLabel_'+tag_id;
}
