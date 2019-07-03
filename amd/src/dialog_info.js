define(
    [
        'jquery',
        'core/modal_factory',
        'core/fragment'
    ],
    /**
     * A general purpose info dialog - makes it easy to show and hide a reusable dialog.
     * @param $
     * @param ModalFactory
     * @returns {dialogInfo}
     */
    function($, ModalFactory, Fragment) {
        var dialogNumber = 0;

        return function DialogInfo(title, bodyHTML, footerHTML, large, autoShow, contextid) {
            this.contextId = contextid;
            DialogInfo.prototype.setupFormModal = function(modal) {
                this.modal.getRoot().on('click', 'form input[type=submit]', this.submitButton.bind(this));
                this.modal.getRoot().on('submit', 'form', this.submitFormAjax.bind(this));

            }

            DialogInfo.prototype.submitButton = function(e) {
                var form = this.modal.getRoot().find('form'),
                    target = $(e.target);
                debugger;
                if (target.attr('name') === 'add_fields') {
                    // $repeats = $('[name$="extended_feedback_repeats"]').val();
                    // $('[name$="extended_feedback_repeats"]').val(+$repeats + 1);
                    var formData = form.serialize();
                    formData = formData + '&' + encodeURIComponent(target.attr('name')) + '=' + encodeURIComponent(target.attr('value'));
                    this.modal.setBody(this.getBody(formData)); // loads fragment only, without form submission.
                    // } else {
                    //     this.submitFormAjax(e); // does the full submission
                    // }
                }
            }
            DialogInfo.prototype.getBody = function(formdata) {

                var params = null;
                if (typeof formdata !== "undefined") {
                    params = { jsonformdata: JSON.stringify(formdata) };
                }
                // Get the content of the modal.
                debugger;
                return Fragment.loadFragment("qtype_wordselect", "feedbackedit", this.contextId, params);
            };

            DialogInfo.prototype.submitFormAjax = function(e) {
                // We don't want to do a real form submission.
                e.preventDefault();
                debugger;
                // Convert all the form elements values to a serialised string.
                var formData = this.modal.getRoot().find('form').serialize();

            }
            var self = this;

            this.modal = null;
            this.dialogNum = 0;
            this.dialogIds = []; // Initialised dialog ids.

            this.restoreFooterDefault = function() {
                // TODO, localise OK.
                var id = 'info_dialog_' + this.dialogNum;
                var okId = id + '_ok';
                this.modal.setFooter('<button id="' + okId + '" class="btn btn-primary">OK</button>');

                if (this.dialogIds.indexOf(this.dialogNum) === -1) {
                    $('body').on('click', '#' + okId, function() {
                        self.modal.hide();
                    });
                }
            };

            this.show = function(title, bodyHTML, footerHTML, large) {
                this.modal.setTitle(title);
                this.modal.setBody(bodyHTML);
                if (footerHTML) {
                    this.modal.setFooter(footerHTML);
                } else {
                    this.restoreFooterDefault();
                }
                this.modal.setLarge(large ? true : false);
                this.modal.show();
            };

            this.hide = function() {
                this.modal.hide();
            };

            autoShow = autoShow === false ? false : true; // Default value is to auto show dialog on creation.

            if (this.modal) {
                var modal = this.modal;
                modal.setBody(bodyHTML);
                if (autoShow) {
                    modal.show();
                }
            } else {
                dialogNumber++;
                this.dialogNum = dialogNumber;

                ModalFactory.create({
                    title: title,
                    body: bodyHTML,
                    footer: footerHTML,
                    large: large,
                }).then(function(modal) {
                    self.modal = modal;
                    if (!footerHTML) {
                        self.restoreFooterDefault();
                    }
                    if (autoShow) {
                        modal.show();
                    }
                    self.dialogIds.push(self.dialogNum); // Dialog is now initialised so register id.
                }).done(function(modal) {
                    this.setupFormModal(modal);
                }.bind(this));
            }
        };
    }
);