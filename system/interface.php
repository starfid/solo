<?php

	interface Primary {
		function primary();
	}

	interface POS {
		function pos($arg);
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

	interface Link {
		function link($arg);
	}

?>