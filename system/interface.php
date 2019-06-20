<?php

	interface Init {
		function init();
	}

	interface Search {
		function search($arg);
	}

	interface Compose {		
		function compose($arg);
	}
	
	interface Update {
		function update($arg);
	}

	interface Delete {
		function delete($arg);
	}
	interface Addtoprinter {
		function addtoprinter($arg);
	}
	interface Printing {
		function printing($arg);
	}

	interface Duplicate {
		function duplicate($arg);
	}
	
	interface Link {
		function link($arg);
	}
	
	interface Merge {
		function merge($arg);	
	}
?>