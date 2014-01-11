Gapfill question type V1.4 for Moodle 

A simpler Cloze question type that only supports fill the the blank type questions. 
Teacher can define the question with square braces to define the missing words. For example
The [cat] sat on the [mat]. Alternative delimiting characters can be defined during question edit
for example The #cat# sat on the #mat# can be valid.

If the dropdown or dragdrop options are set in the question edit form, it can display 
a shuffled selection  of correct and wrong answers. These can then can be selected via 
dropddropdown lists or javascript powered drag and drop functionality.

This question type was written by Marcus Green

This question type was created and tested under Moodle 2.5 and 2.6. It has also been tested with Moodle 2.4.
It will not work with versions of moodle prior to 2.1.

Place the files in a directory 

moodle\question\type\gapfill

Where moodle is webroot for your install.

Version 1.1 includes a count of correct answers and clears incorrect responses in interactive mode
Version 1.2 will colour duplicate answers yellow when discard duplicates mode is used (see help)
Version 1.3 toggle regex for plain string compare. Useful for maths, html and programming language questions
Version 1.4 added support for touch devices such as Apple iOS phones and tablets (iPhone/iPad) and Android devices 
