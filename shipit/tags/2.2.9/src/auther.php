<?php

function shipit_json_basic_auth_handler( $user ) {
	global $wp_json_basic_auth_error;
	
	$wp_json_basic_auth_error = null;
	
	
	if ( ! empty( $user ) ) {
		return $user;
	}
	
	if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
		return $user;
	}
	
	$username = $_SERVER['PHP_AUTH_USER'];
	$password = $_SERVER['PHP_AUTH_PW'];
	
	
	remove_filter( 'determine_current_user', 'shipit_json_basic_auth_handler', 20 );
	
	$user = wp_authenticate( $username, $password );
	
	add_filter( 'determine_current_user', 'shipit_json_basic_auth_handler', 20 );
	
	if ( is_wp_error( $user ) ) {
		$wp_json_basic_auth_error = $user;
		return null;
	}
	
	$wp_json_basic_auth_error = true;
	
	return $user->ID;
}
add_filter( 'determine_current_user', 'shipit_json_basic_auth_handler', 20 );

function shipit_json_basic_auth_error( $error ) {
	
	if ( ! empty( $error ) ) {
		return $error;
	}
	
	global $wp_json_basic_auth_error;
	
	return $wp_json_basic_auth_error;
}
add_filter( 'rest_authentication_errors', 'shipit_json_basic_auth_error' );
add_filter('woocommerce_states', 'comunas_de_chile');
function comunas_de_chile($states) {
	$states['CL'] = array(
		'CL64' => __("HIJUELAS", 'woocommerce'),
		'CL10' => __("HUARA", 'woocommerce'),
		'CL36' => __("ILLAPEL", 'woocommerce'),
		'CL262' => __("QUINCHAO", 'woocommerce'),
		'CL65' => __("LA CRUZ", 'woocommerce'),
		'CL86' => __("COLTAUCO", 'woocommerce'),
		'CL89' => __("LAS CABRAS", 'woocommerce'),
		'CL47' => __("CONCON", 'woocommerce'),
		'CL37' => __("CANELA", 'woocommerce'),
		'CL91' => __("MALLOA", 'woocommerce'),
		'CL13' => __("MEJILLONES", 'woocommerce'),
		'CL66' => __("NOGALES", 'woocommerce'),
		'CL40' => __("OVALLE", 'woocommerce'),
		'CL11' => __("PICA", 'woocommerce'),
		'CL22' => __("CALDERA", 'woocommerce'),
		'CL50' => __("QUINTERO", 'woocommerce'),
		'CL15' => __("TALTAL", 'woocommerce'),
		'CL39' => __("SALAMANCA", 'woocommerce'),
		'CL45' => __("VALPARAISO", 'woocommerce'),
		'CL46' => __("CASABLANCA", 'woocommerce'),
		'CL85' => __("COINCO", 'woocommerce'),
		'CL82' => __("VILLA ALEMANA", 'woocommerce'),
		'CL61' => __("ZAPALLAR", 'woocommerce'),
		'CL77' => __("PUTAENDO", 'woocommerce'),
		'CL75' => __("LLAILLAY", 'woocommerce'),
		'CL59' => __("PAPUDO", 'woocommerce'),
		'CL338' => __("ALHUE", 'woocommerce'),
		'CL730' => __("SOCAIRE", 'woocommerce'),
		'CL4' => __("GENERAL LAGOS", 'woocommerce'),
		'CL758' => __("VILLA PRAT", 'woocommerce'),
		'CL58' => __("CABILDO", 'woocommerce'),
		'CL52' => __("ISLA DE PASCUA", 'woocommerce'),
		'CL181' => __("COBQUECURA", 'woocommerce'),
		'CL165' => __("LOS ANGELES", 'woocommerce'),
		'CL127' => __("CHANCO", 'woocommerce'),
		'CL168' => __("LAJA", 'woocommerce'),
		'CL133' => __("RAUCO", 'woocommerce'),
		'CL6' => __("ALTO HOSPICIO", 'woocommerce'),
		'CL140' => __("LONGAVI", 'woocommerce'),
		'CL132' => __("MOLINA", 'woocommerce'),
		'CL169' => __("MULCHEN", 'woocommerce'),
		'CL128' => __("PELLUHUE", 'woocommerce'),
		'CL93' => __("OLIVAR", 'woocommerce'),
		'CL100' => __("PICHILEMU", 'woocommerce'),
		'CL135' => __("SAGRADA FAMILIA", 'woocommerce'),
		'CL173' => __("QUILLECO", 'woocommerce'),
		'CL164' => __("TIRUA", 'woocommerce'),
		'CL98' => __("REQUINOA", 'woocommerce'),
		'CL161' => __("CONTULMO", 'woocommerce'),
		'CL129' => __("CURICO", 'woocommerce'),
		'CL183' => __("COIHUECO", 'woocommerce'),
		'CL136' => __("TENO", 'woocommerce'),
		'CL125' => __("SAN RAFAEL", 'woocommerce'),
		'CL177' => __("YUMBEL", 'woocommerce'),
		'CL103' => __("MARCHIGUE", 'woocommerce'),
		'CL102' => __("LITUECHE", 'woocommerce'),
		'CL160' => __("CANETE", 'woocommerce'),
		'CL114' => __("PUMANQUE", 'woocommerce'),
		'CL106' => __("SAN FERNANDO", 'woocommerce'),
		'CL738' => __("TIGNAMAR", 'woocommerce'),
		'CL19' => __("TOCOPILLA", 'woocommerce'),
		'CL62' => __("QUILLOTA", 'woocommerce'),
		'CL56' => __("SAN ESTEBAN", 'woocommerce'),
		'CL116' => __("TALCA", 'woocommerce'),
		'CL27' => __("ALTO DEL CARMEN", 'woocommerce'),
		'CL232' => __("VALDIVIA", 'woocommerce'),
		'CL199' => __("YUNGAY", 'woocommerce'),
		'CL202' => __("CUNCO", 'woocommerce'),
		'CL94' => __("PEUMO", 'woocommerce'),
		'CL273' => __("PALENA", 'woocommerce'),
		'CL162' => __("CURANILAHUE", 'woocommerce'),
		'CL193' => __("RANQUIL", 'woocommerce'),
		'CL228' => __("PUREN", 'woocommerce'),
		'CL197' => __("SAN NICOLAS", 'woocommerce'),
		'CL227' => __("LUMACO", 'woocommerce'),
		'CL238' => __("PAILLACO", 'woocommerce'),
		'CL196' => __("SAN IGNACIO", 'woocommerce'),
		'CL256' => __("CURACO DE VELEZ", 'woocommerce'),
		'CL242' => __("LAGO RANCO", 'woocommerce'),
		'CL236' => __("MAFIL", 'woocommerce'),
		'CL188' => __("PEMUCO", 'woocommerce'),
		'CL194' => __("SAN CARLOS", 'woocommerce'),
		'CL217' => __("TOLTEN", 'woocommerce'),
		'CL230' => __("TRAIGUEN", 'woocommerce'),
		'CL443' => __("ARTIFICIO", 'woocommerce'),
		'CL225' => __("LONQUIMAY", 'woocommerce'),
		'CL234' => __("LANCO", 'woocommerce'),
		'CL244' => __("PUERTO MONTT", 'woocommerce'),
		'CL222' => __("COLLIPULLI", 'woocommerce'),
		'CL250' => __("LLANQUIHUE", 'woocommerce'),
		'CL166' => __("ANTUCO", 'woocommerce'),
		'CL271' => __("FUTALEUFU", 'woocommerce'),
		'CL451' => __("BELLOTO", 'woocommerce'),
		'CL179' => __("CHILLAN", 'woocommerce'),
		'CL224' => __("ERCILLA", 'woocommerce'),
		'CL760' => __("VILLUCO", 'woocommerce'),
		'CL249' => __("LOS MUERMOS", 'woocommerce'),
		'CL428' => __("FLORIDA", 'woocommerce'),
		'CL277' => __("PUERTO CISNES", 'woocommerce'),
		'CL516' => __("EL COLORADO", 'woocommerce'),
		'CL466' => __("CAMPANARIO", 'woocommerce'),
		'CL283' => __("RIO IBANEZ", 'woocommerce'),
		'CL274' => __("COYHAIQUE", 'woocommerce'),
		'CL350' => __("CACHAPOAL", 'woocommerce'),
		'CL57' => __("LA LIGUA", 'woocommerce'),
		'CL366' => __("CIUDAD DE LOS VALLES", 'woocommerce'),
		'CL352' => __("PICHIDEGUA", 'woocommerce'),
		'CL364' => __("BATUCO", 'woocommerce'),
		'CL31' => __("COQUIMBO", 'woocommerce'),
		'CL180' => __("BULNES", 'woocommerce'),
		'CL290' => __("PORVENIR", 'woocommerce'),
		'CL336' => __("PAINE", 'woocommerce'),
		'CL285' => __("LAGUNA BLANCA", 'woocommerce'),
		'CL568' => __("LA PLACILLA (PUNITAQUI)", 'woocommerce'),
		'CL337' => __("MELIPILLA", 'woocommerce'),
		'CL667' => __("PUAUCHO", 'woocommerce'),
		'CL344' => __("ISLA DE MAIPO", 'woocommerce'),
		'CL435' => __("TIL TIL", 'woocommerce'),
		'CL292' => __("TIMAUKEL", 'woocommerce'),
		'CL468' => __("CAPITAN PASTENE", 'woocommerce'),
		'CL289' => __("ANTARTICA", 'woocommerce'),
		'CL363' => __("RENACA", 'woocommerce'),
		'CL473' => __("CARIQUIMA", 'woocommerce'),
		'CL761' => __("VISVIRI", 'woocommerce'),
		'CL281' => __("TORTEL", 'woocommerce'),
		'CL356' => __("HORNOPIREN", 'woocommerce'),
		'CL2' => __("CAMARONES", 'woocommerce'),
		'CL424' => __("ALERCE", 'woocommerce'),
		'CL167' => __("CABRERO", 'woocommerce'),
		'CL16' => __("CALAMA", 'woocommerce'),
		'CL146' => __("CONCEPCION", 'woocommerce'),
		'CL405' => __("AYSEN", 'woocommerce'),
		'CL245' => __("CALBUCO", 'woocommerce'),
		'CL54' => __("CALLE LARGA", 'woocommerce'),
		'CL72' => __("SANTO DOMINGO", 'woocommerce'),
		'CL410' => __("ACHAO", 'woocommerce'),
		'CL416' => __("BATUCO (TALCA)", 'woocommerce'),
		'CL385' => __("CHAMPA", 'woocommerce'),
		'CL399' => __("CALETA BUENA", 'woocommerce'),
		'CL390' => __("CURIMON", 'woocommerce'),
		'CL387' => __("CHOCALAN", 'woocommerce'),
		'CL393' => __("EL MEMBRILLO", 'woocommerce'),
		'CL1' => __("ARICA", 'woocommerce'),
		'CL398' => __("POMAIRE", 'woocommerce'),
		'CL406' => __("LA JUNTA", 'woocommerce'),
		'CL371' => __("LAS VERTIENTES", 'woocommerce'),
		'CL425' => __("LAS CARDAS", 'woocommerce'),
		'CL407' => __("LLOLLEO", 'woocommerce'),
		'CL414' => __("PLAYA ANCHA", 'woocommerce'),
		'CL376' => __("MAIPO", 'woocommerce'),
		'CL378' => __("NOVICIADO", 'woocommerce'),
		'CL381' => __("SAN GABRIEL", 'woocommerce'),
		'CL402' => __("TONGOY", 'woocommerce'),
		'CL423' => __("EL CARMEN CHILLAN", 'woocommerce'),
		'CL49' => __("PUCHUNCAVI", 'woocommerce'),
		'CL8' => __("CAMINA", 'woocommerce'),
		'CL334' => __("BUIN", 'woocommerce'),
		'CL221' => __("ANGOL", 'woocommerce'),
		'CL373' => __("LO VALDES", 'woocommerce'),
		'CL247' => __("FRESIA", 'woocommerce'),
		'CL148' => __("CHIGUAYANTE", 'woocommerce'),
		'CL279' => __("COCHRANE", 'woocommerce'),
		'CL139' => __("COLBUN", 'woocommerce'),
		'CL233' => __("CORRAL", 'woocommerce'),
		'CL248' => __("FRUTILLAR", 'woocommerce'),
		'CL203' => __("CURARREHUE", 'woocommerce'),
		'CL201' => __("CARAHUE", 'woocommerce'),
		'CL241' => __("FUTRONO", 'woocommerce'),
		'CL41' => __("COMBARBALA", 'woocommerce'),
		'CL347' => __("LA CALERA", 'woocommerce'),
		'CL147' => __("CORONEL", 'woocommerce'),
		'CL5' => __("IQUIQUE", 'woocommerce'),
		'CL74' => __("CATEMU", 'woocommerce'),
		'CL21' => __("COPIAPO", 'woocommerce'),
		'CL282' => __("CHILE CHICO", 'woocommerce'),
		'CL70' => __("EL QUISCO", 'woocommerce'),
		'CL204' => __("FREIRE", 'woocommerce'),
		'CL28' => __("FREIRINA", 'woocommerce'),
		'CL206' => __("GORBEA", 'woocommerce'),
		'CL88' => __("GRANEROS", 'woocommerce'),
		'CL29' => __("HUASCO", 'woocommerce'),
		'CL101' => __("LA ESTRELLA", 'woocommerce'),
		'CL33' => __("LA HIGUERA", 'woocommerce'),
		'CL343' => __("EL MONTE", 'woocommerce'),
		'CL157' => __("HUALPEN", 'woocommerce'),
		'CL246' => __("COCHAMO", 'woocommerce'),
		'CL119' => __("EMPEDRADO", 'woocommerce'),
		'CL220' => __("CHOLCHOL", 'woocommerce'),
		'CL464' => __("CAMARICO", 'woocommerce'),
		'CL739' => __("TOCONAO", 'woocommerce'),
		'CL278' => __("GUAITECAS", 'woocommerce'),
		'CL185' => __("EL CARMEN", 'woocommerce'),
		'CL87' => __("DONIHUE", 'woocommerce'),
		'CL429' => __("OHIGGINS", 'woocommerce'),
		'CL120' => __("MAULE", 'woocommerce'),
		'CL432' => __("PALMILLA", 'woocommerce'),
		'CL122' => __("PENCAHUE", 'woocommerce'),
		'CL345' => __("PADRE HURTADO", 'woocommerce'),
		'CL38' => __("LOS VILOS", 'woocommerce'),
		'CL158' => __("LEBU", 'woocommerce'),
		'CL18' => __("SAN PEDRO DE ATACAMA", 'woocommerce'),
		'CL263' => __("OSORNO", 'woocommerce'),
		'CL151' => __("LOTA", 'woocommerce'),
		'CL212' => __("PERQUENCO", 'woocommerce'),
		'CL430' => __("OLLAGUE", 'woocommerce'),
		'CL209' => __("MELIPEUCO", 'woocommerce'),
		'CL109' => __("LOLOL", 'woocommerce'),
		'CL121' => __("PELARCO", 'woocommerce'),
		'CL81' => __("OLMUE", 'woocommerce'),
		'CL112' => __("PERALILLO", 'woocommerce'),
		'CL237' => __("MARIQUINA", 'woocommerce'),
		'CL84' => __("CODEGUA", 'woocommerce'),
		'CL76' => __("PANQUEHUE", 'woocommerce'),
		'CL431' => __("PAIHUANO", 'woocommerce'),
		'CL226' => __("LOS SAUCES", 'woocommerce'),
		'CL171' => __("NEGRETE", 'woocommerce'),
		'CL187' => __("NIQUEN", 'woocommerce'),
		'CL331' => __("LAMPA", 'woocommerce'),
		'CL163' => __("LOS ALAMOS", 'woocommerce'),
		'CL80' => __("LIMACHE", 'woocommerce'),
		'CL219' => __("VILLARRICA", 'woocommerce'),
		'CL53' => __("LOS ANDES", 'woocommerce'),
		'CL340' => __("MARIA PINTO", 'woocommerce'),
		'CL620' => __("MEHUIN", 'woocommerce'),
		'CL141' => __("PARRAL", 'woocommerce'),
		'CL258' => __("PUQUELDON", 'woocommerce'),
		'CL286' => __("RIO VERDE", 'woocommerce'),
		'CL418' => __("PLACILLA (VINA DEL MAR)", 'woocommerce'),
		'CL214' => __("PUCON", 'woocommerce'),
		'CL97' => __("RENGO", 'woocommerce'),
		'CL265' => __("PURRANQUE", 'woocommerce'),
		'CL215' => __("SAAVEDRA", 'woocommerce'),
		'CL205' => __("GALVARINO", 'woocommerce'),
		'CL268' => __("SAN JUAN DE LA COSTA", 'woocommerce'),
		'CL83' => __("RANCAGUA", 'woocommerce'),
		'CL124' => __("SAN CLEMENTE", 'woocommerce'),
		'CL67' => __("SAN ANTONIO", 'woocommerce'),
		'CL7' => __("POZO ALMONTE", 'woocommerce'),
		'CL143' => __("SAN JAVIER", 'woocommerce'),
		'CL634' => __("NELTUME", 'woocommerce'),
		'CL71' => __("EL TABO", 'woocommerce'),
		'CL287' => __("SAN GREGORIO", 'woocommerce'),
		'CL182' => __("COELEMU", 'woocommerce'),
		'CL44' => __("RIO HURTADO", 'woocommerce'),
		'CL3' => __("PUTRE", 'woocommerce'),
		'CL433' => __("SAN PEDRO DE MELIPILLA", 'woocommerce'),
		'CL229' => __("RENAICO", 'woocommerce'),
		'CL154' => __("SANTA JUANA", 'woocommerce'),
		'CL517' => __("EL HINOJAL", 'woocommerce'),
		'CL539' => __("GUARDIA VIEJA", 'woocommerce'),
		'CL73' => __("SAN FELIPE", 'woocommerce'),
		'CL765' => __("BARROS ARANA", 'woocommerce'),
		'CL113' => __("PLACILLA", 'woocommerce'),
		'CL142' => __("RETIRO", 'woocommerce'),
		'CL78' => __("SANTA MARIA", 'woocommerce'),
		'CL55' => __("RINCONADA", 'woocommerce'),
		'CL12' => __("ANTOFAGASTA", 'woocommerce'),
		'CL191' => __("QUILLON", 'woocommerce'),
		'CL200' => __("TEMUCO", 'woocommerce'),
		'CL131' => __("LICANTEN", 'woocommerce'),
		'CL445' => __("BAHIA MANSA", 'woocommerce'),
		'CL26' => __("VALLENAR", 'woocommerce'),
		'CL444' => __("BAHIA INGLESA", 'woocommerce'),
		'CL463' => __("CALETONES", 'woocommerce'),
		'CL447' => __("BALMACEDA", 'woocommerce'),
		'CL450' => __("BELEN", 'woocommerce'),
		'CL178' => __("ALTO BIOBIO", 'woocommerce'),
		'CL452' => __("BOBADILLA", 'woocommerce'),
		'CL156' => __("TOME", 'woocommerce'),
		'CL453' => __("BUCALEMU", 'woocommerce'),
		'CL455' => __("CABURGA", 'woocommerce'),
		'CL454' => __("BUCHUPUREO", 'woocommerce'),
		'CL457' => __("CAHUIL", 'woocommerce'),
		'CL459' => __("CAJON", 'woocommerce'),
		'CL400' => __("ANCUAQUE", 'woocommerce'),
		'CL460' => __("CALAFQUEN", 'woocommerce'),
		'CL462' => __("CALETA GONZALO", 'woocommerce'),
		'CL461' => __("CALETA ANDRADE", 'woocommerce'),
		'CL465' => __("CAMERON", 'woocommerce'),
		'CL145' => __("YERBAS BUENAS", 'woocommerce'),
		'CL449' => __("BARRANCAS", 'woocommerce'),
		'CL137' => __("VICHUQUEN", 'woocommerce'),
		'CL35' => __("VICUNA", 'woocommerce'),
		'CL384' => __("BOLLENAR", 'woocommerce'),
		'CL437' => __("AHUI", 'woocommerce'),
		'CL438' => __("AIQUINA", 'woocommerce'),
		'CL439' => __("ALGARROBAL", 'woocommerce'),
		'CL294' => __("TORRES DEL PAINE", 'woocommerce'),
		'CL446' => __("BAHIA MURTA", 'woocommerce'),
		'CL150' => __("HUALQUI", 'woocommerce'),
		'CL441' => __("ALICAHUE", 'woocommerce'),
		'CL448' => __("BAQUEDANO", 'woocommerce'),
		'CL130' => __("HUALANE", 'woocommerce'),
		'CL492' => __("COBIJA", 'woocommerce'),
		'CL388' => __("CODIGUA", 'woocommerce'),
		'CL494' => __("CODPA", 'woocommerce'),
		'CL472' => __("CAREN", 'woocommerce'),
		'CL496' => __("COLIUMO", 'woocommerce'),
		'CL152' => __("PENCO", 'woocommerce'),
		'CL469' => __("CAQUENA", 'woocommerce'),
		'CL497' => __("COLLACAGUA", 'woocommerce'),
		'CL499' => __("CONAY", 'woocommerce'),
		'CL500' => __("COSAPILLA", 'woocommerce'),
		'CL501' => __("COYA", 'woocommerce'),
		'CL503' => __("CULENAR", 'woocommerce'),
		'CL504' => __("CUMPEO", 'woocommerce'),
		'CL505' => __("CUNACO", 'woocommerce'),
		'CL506' => __("CUNCUMEN", 'woocommerce'),
		'CL508' => __("CURINANCO", 'woocommerce'),
		'CL510' => __("DEGAN", 'woocommerce'),
		'CL511' => __("DICHATO", 'woocommerce'),
		'CL509' => __("CUYA", 'woocommerce'),
		'CL513' => __("DUAO", 'woocommerce'),
		'CL391' => __("EL CANELO", 'woocommerce'),
		'CL470' => __("CARAMPANGUE", 'woocommerce'),
		'CL471' => __("CARELMAPU", 'woocommerce'),
		'CL386' => __("CHICUREO", 'woocommerce'),
		'CL401' => __("CANCOSA", 'woocommerce'),
		'CL482' => __("CHANARAL ALTO", 'woocommerce'),
		'CL483' => __("CHANARAL DE CAREN", 'woocommerce'),
		'CL486' => __("CHEPU", 'woocommerce'),
		'CL412' => __("CURAUMA", 'woocommerce'),
		'CL487' => __("CHERCHENCO", 'woocommerce'),
		'CL467' => __("CANTO DE AGUA", 'woocommerce'),
		'CL484' => __("CHARRUA", 'woocommerce'),
		'CL562' => __("LA HAUYCA", 'woocommerce'),
		'CL488' => __("CHILLEPIN", 'woocommerce'),
		'CL485' => __("CHAUQUEN", 'woocommerce'),
		'CL174' => __("SAN ROSENDO", 'woocommerce'),
		'CL529' => __("ENTRE LAGOS", 'woocommerce'),
		'CL530' => __("ESQUINA", 'woocommerce'),
		'CL524' => __("EL SAUCE", 'woocommerce'),
		'CL176' => __("TUCAPEL", 'woocommerce'),
		'CL532' => __("FARELLONES", 'woocommerce'),
		'CL534' => __("GUADAL", 'woocommerce'),
		'CL535' => __("GUALLECO", 'woocommerce'),
		'CL552' => __("HUINTIL", 'woocommerce'),
		'CL538' => __("GUAPILACUY", 'woocommerce'),
		'CL541' => __("HORCON", 'woocommerce'),
		'CL542' => __("HORCON IV", 'woocommerce'),
		'CL518' => __("EL MANZANO", 'woocommerce'),
		'CL550' => __("HUEPIL", 'woocommerce'),
		'CL544' => __("HOSPITAL", 'woocommerce'),
		'CL545' => __("HUALPENCILLO", 'woocommerce'),
		'CL547' => __("HUELDEN", 'woocommerce'),
		'CL548' => __("HUELLAHUE", 'woocommerce'),
		'CL551' => __("HUILLINCO", 'woocommerce'),
		'CL553' => __("ICALMA", 'woocommerce'),
		'CL554' => __("ILOCA", 'woocommerce'),
		'CL521' => __("EL PAICO", 'woocommerce'),
		'CL556' => __("ISLA TEJA", 'woocommerce'),
		'CL557' => __("ISLUGA", 'woocommerce'),
		'CL536' => __("GUANAQUEROS", 'woocommerce'),
		'CL561' => __("LA CHIMBA", 'woocommerce'),
		'CL558' => __("ITAHUE", 'woocommerce'),
		'CL368' => __("EL INGENIO", 'woocommerce'),
		'CL563' => __("LA JARILLA", 'woocommerce'),
		'CL564' => __("LA MARQUESA", 'woocommerce'),
		'CL565' => __("LA NEGRA", 'woocommerce'),
		'CL566' => __("LA OBRA", 'woocommerce'),
		'CL559' => __("LA ARENA", 'woocommerce'),
		'CL523' => __("EL PENON", 'woocommerce'),
		'CL525' => __("EL TABITO", 'woocommerce'),
		'CL115' => __("SANTA CRUZ", 'woocommerce'),
		'CL579' => __("LAS CRUCES", 'woocommerce'),
		'CL581' => __("LAS DUNAS", 'woocommerce'),
		'CL573' => __("LAGUNA VERDE", 'woocommerce'),
		'CL584' => __("LAS TACAS", 'woocommerce'),
		'CL585' => __("LAS TRANCAS", 'woocommerce'),
		'CL24' => __("CHANARAL", 'woocommerce'),
		'CL587' => __("LASANA", 'woocommerce'),
		'CL588' => __("LECHAGUA", 'woocommerce'),
		'CL589' => __("LEYDA", 'woocommerce'),
		'CL590' => __("LICANRAY", 'woocommerce'),
		'CL591' => __("LIMARI", 'woocommerce'),
		'CL592' => __("LINAO", 'woocommerce'),
		'CL594' => __("LIRIMA", 'woocommerce'),
		'CL595' => __("LIRQUEN", 'woocommerce'),
		'CL396' => __("LO CHACON", 'woocommerce'),
		'CL597' => __("LLICO", 'woocommerce'),
		'CL599' => __("LO MIRANDA", 'woocommerce'),
		'CL570' => __("LA TAPERA", 'woocommerce'),
		'CL600' => __("LO VALDIVIA", 'woocommerce'),
		'CL603' => __("LOS HORCONES", 'woocommerce'),
		'CL604' => __("LOS HORNOS", 'woocommerce'),
		'CL576' => __("LARAQUETE", 'woocommerce'),
		'CL605' => __("LOS LAURELES", 'woocommerce'),
		'CL608' => __("LOS MOLLES", 'woocommerce'),
		'CL609' => __("LOS NICHES", 'woocommerce'),
		'CL610' => __("LOS OLIVOS", 'woocommerce'),
		'CL611' => __("MAICOLPUE", 'woocommerce'),
		'CL645' => __("PAN DE AZUCAR", 'woocommerce'),
		'CL614' => __("MANAO", 'woocommerce'),
		'CL370' => __("LAGUNA ACULEO", 'woocommerce'),
		'CL582' => __("LAS NIEVES", 'woocommerce'),
		'CL571' => __("LA TIRANA", 'woocommerce'),
		'CL426' => __("LAS CARDAS SUR", 'woocommerce'),
		'CL372' => __("LINDEROS", 'woocommerce'),
		'CL186' => __("NINHUE", 'woocommerce'),
		'CL628' => __("MONTE AYMOND", 'woocommerce'),
		'CL629' => __("MONTE GRANDE", 'woocommerce'),
		'CL631' => __("MORRILLOS", 'woocommerce'),
		'CL636' => __("NIPAS", 'woocommerce'),
		'CL638' => __("NIRIVILO", 'woocommerce'),
		'CL615' => __("MANDINGA", 'woocommerce'),
		'CL640' => __("NUEVA BRAUNAU", 'woocommerce'),
		'CL641' => __("PACHAMA", 'woocommerce'),
		'CL643' => __("PALOMAR", 'woocommerce'),
		'CL642' => __("PAIPOTE", 'woocommerce'),
		'CL646' => __("PANGAL", 'woocommerce'),
		'CL647' => __("PANIMAVIDA", 'woocommerce'),
		'CL648' => __("PAPOSO", 'woocommerce'),
		'CL621' => __("MELINKA", 'woocommerce'),
		'CL650' => __("PARGUA", 'woocommerce'),
		'CL651' => __("PAULDEO", 'woocommerce'),
		'CL652' => __("PEDREGAL", 'woocommerce'),
		'CL654' => __("PENABLANCA", 'woocommerce'),
		'CL655' => __("PETROHUE", 'woocommerce'),
		'CL656' => __("PICHASCA", 'woocommerce'),
		'CL657' => __("PICHI PELLUCO", 'woocommerce'),
		'CL658' => __("PICHICUY", 'woocommerce'),
		'CL661' => __("PINGUERAL", 'woocommerce'),
		'CL662' => __("PISCO ELQUI", 'woocommerce'),
		'CL663' => __("POCONCHILE", 'woocommerce'),
		'CL618' => __("MATANZAS", 'woocommerce'),
		'CL664' => __("POLCURA", 'woocommerce'),
		'CL644' => __("PALQUIAL", 'woocommerce'),
		'CL622' => __("MINAS DEL PRADO", 'woocommerce'),
		'CL635' => __("NIEBLA", 'woocommerce'),
		'CL380' => __("NOS", 'woocommerce'),
		'CL625' => __("MOCHA", 'woocommerce'),
		'CL189' => __("PINTO", 'woocommerce'),
		'CL383' => __("ALTO JAHUEL", 'woocommerce'),
		'CL672' => __("PUENTE QUILO", 'woocommerce'),
		'CL714' => __("ROSARIO", 'woocommerce'),
		'CL676' => __("PUERTO BERTRAND", 'woocommerce'),
		'CL675' => __("PUERTO ALDEA", 'woocommerce'),
		'CL671' => __("PUENTE NEGRO", 'woocommerce'),
		'CL240' => __("LA UNION", 'woocommerce'),
		'CL673' => __("PUERTECILLO", 'woocommerce'),
		'CL522' => __("EL PALQUI", 'woocommerce'),
		'CL682' => __("PUERTO WILLIAMS", 'woocommerce'),
		'CL677' => __("PUERTO BORIES", 'woocommerce'),
		'CL679' => __("PUERTO TORO", 'woocommerce'),
		'CL716' => __("RUCAPEQUEN", 'woocommerce'),
		'CL705' => __("RAPEL", 'woocommerce'),
		'CL669' => __("PUEBLO SECO", 'woocommerce'),
		'CL706' => __("RAYENCURA", 'woocommerce'),
		'CL668' => __("PUCATRIHUE", 'woocommerce'),
		'CL713' => __("ROBLE HUACHO", 'woocommerce'),
		'CL707' => __("RECOLETA IV", 'woocommerce'),
		'CL708' => __("RIHUE", 'woocommerce'),
		'CL709' => __("RINCONADA DE GUZMAN", 'woocommerce'),
		'CL710' => __("RINCONADA DE SILVA", 'woocommerce'),
		'CL711' => __("RINIHUE", 'woocommerce'),
		'CL218' => __("VILCUN", 'woocommerce'),
		'CL68' => __("ALGARROBO", 'woocommerce'),
		'CL107' => __("CHEPICA", 'woocommerce'),
		'CL190' => __("PORTEZUELO", 'woocommerce'),
		'CL392' => __("EL MELOCOTON", 'woocommerce'),
		'CL192' => __("QUIRIHUE", 'woocommerce'),
		'CL759' => __("VILLA SANTA LUCIA", 'woocommerce'),
		'CL369' => __("HUELQUEN", 'woocommerce'),
		'CL377' => __("MALLOCO", 'woocommerce'),
		'CL159' => __("ARAUCO", 'woocommerce'),
		'CL575' => __("LAJAS BLANCAS", 'woocommerce'),
		'CL724' => __("SAN PEDRO QUINTA REGION", 'woocommerce'),
		'CL731' => __("SOCOROMA", 'woocommerce'),
		'CL408' => __("SOTOCA", 'woocommerce'),
		'CL763' => __("ZEMITA", 'woocommerce'),
		'CL733' => __("SORA", 'woocommerce'),
		'CL734' => __("SOTAQUI", 'woocommerce'),
		'CL404' => __("NAVIDAD", 'woocommerce'),
		'CL48' => __("JUAN FERNANDEZ", 'woocommerce'),
		'CL756' => __("VILLA MERCEDES", 'woocommerce'),
		'CL270' => __("CHAITEN", 'woocommerce'),
		'CL719' => __("SAN JUAN", 'woocommerce'),
		'CL735' => __("TAMBILLO", 'woocommerce'),
		'CL737' => __("TIERRAS BLANCAS", 'woocommerce'),
		'CL736' => __("TANILVORO", 'woocommerce'),
		'CL342' => __("TALAGANTE", 'woocommerce'),
		'CL434' => __("SAN VICENTE DE TAGUA TAGUA", 'woocommerce'),
		'CL198' => __("TREGUACO", 'woocommerce'),
		'CL375' => __("LONQUEN", 'woocommerce'),
		'CL172' => __("QUILACO", 'woocommerce'),
		'CL117' => __("CONSTITUCION", 'woocommerce'),
		'CL118' => __("CUREPTO", 'woocommerce'),
		'CL69' => __("CARTAGENA", 'woocommerce'),
		'CL108' => __("CHIMBARONGO", 'woocommerce'),
		'CL207' => __("LAUTARO", 'woocommerce'),
		'CL239' => __("PANGUIPULLI", 'woocommerce'),
		'CL329' => __("SAN JOSE DE MAIPO", 'woocommerce'),
		'CL264' => __("PUERTO OCTAY", 'woocommerce'),
		'CL155' => __("TALCAHUANO", 'woocommerce'),
		'CL23' => __("TIERRA AMARILLA", 'woocommerce'),
		'CL440' => __("ALGARROBITO", 'woocommerce'),
		'CL722' => __("SAN MARCOS", 'woocommerce'),
		'CL293' => __("PUERTO NATALES", 'woocommerce'),
		'CL79' => __("QUILPUE", 'woocommerce'),
		'CL138' => __("LINARES", 'woocommerce'),
		'CL25' => __("DIEGO DE ALMAGRO", 'woocommerce'),
		'CL310' => __("LO ESPEJO", 'woocommerce'),
		'CL318' => __("PUDAHUEL", 'woocommerce'),
		'CL740' => __("TOPOCALMA", 'woocommerce'),
		'CL208' => __("LONCOCHE", 'woocommerce'),
		'CL90' => __("MACHALI", 'woocommerce'),
		'CL389' => __("CULIPRAN", 'woocommerce'),
		'CL269' => __("SAN PABLO", 'woocommerce'),
		'CL572' => __("LABRANZA", 'woocommerce'),
		'CL367' => __("ALTO EL CANELO", 'woocommerce'),
		'CL490' => __("CHOSHUENCO", 'woocommerce'),
		'CL491' => __("CIRUELOS", 'woocommerce'),
		'CL110' => __("NANCAGUA", 'woocommerce'),
		'CL259' => __("QUEILEN", 'woocommerce'),
		'CL9' => __("COLCHANE", 'woocommerce'),
		'CL243' => __("RIO BUENO", 'woocommerce'),
		'CL105' => __("PAREDONES", 'woocommerce'),
		'CL479' => __("CERRILLOS DE TAMAYA", 'woocommerce'),
		'CL272' => __("HUALAIHUE", 'woocommerce'),
		'CL309' => __("LO BARNECHEA", 'woocommerce'),
		'CL507' => __("CURANIPE", 'woocommerce'),
		'CL481' => __("CHACAO", 'woocommerce'),
		'CL20' => __("MARIA ELENA", 'woocommerce'),
		'CL235' => __("LOS LAGOS", 'woocommerce'),
		'CL92' => __("MOSTAZAL", 'woocommerce'),
		'CL498' => __("CONARIPE", 'woocommerce'),
		'CL42' => __("MONTE PATRIA", 'woocommerce'),
		'CL549' => __("HUENTELAUQUEN", 'woocommerce'),
		'CL531' => __("ESTACION PAIPOTE", 'woocommerce'),
		'CL543' => __("HORNITOS", 'woocommerce'),
		'CL346' => __("PENAFLOR", 'woocommerce'),
		'CL330' => __("COLINA", 'woocommerce'),
		'CL255' => __("CHONCHI", 'woocommerce'),
		'CL660' => __("PILLANLELBUN", 'woocommerce'),
		'CL413' => __("PUERTO CHACABUCO", 'woocommerce'),
		'CL379' => __("RUNGUE", 'woocommerce'),
		'CL60' => __("PETORCA", 'woocommerce'),
		'CL267' => __("RIO NEGRO", 'woocommerce'),
		'CL134' => __("ROMERAL", 'woocommerce'),
		'CL723' => __("SAN PEDRO DE ALCANTARA", 'woocommerce'),
		'CL354' => __("SAN FRANCISCO DE MOSTAZAL", 'woocommerce'),
		'CL254' => __("ANCUD", 'woocommerce'),
		'CL252' => __("PUERTO VARAS", 'woocommerce'),
		'CL253' => __("CASTRO", 'woocommerce'),
		'CL175' => __("SANTA BARBARA", 'woocommerce'),
		'CL260' => __("QUELLON", 'woocommerce'),
		'CL701' => __("QUIRIQUINA", 'woocommerce'),
		'CL477' => __("CAULIN", 'woocommerce'),
		'CL96' => __("QUINTA DE TILCOCO", 'woocommerce'),
		'CL520' => __("EL MOLLE", 'woocommerce'),
		'CL526' => __("EL TAMBO", 'woocommerce'),
		'CL284' => __("PUNTA ARENAS", 'woocommerce'),
		'CL567' => __("LA PARVA", 'woocommerce'),
		'CL442' => __("ALTOVALSOL", 'woocommerce'),
		'CL476' => __("CATAPILCO", 'woocommerce'),
		'CL480' => __("CERRO SOMBRERO", 'woocommerce'),
		'CL665' => __("POPETA", 'woocommerce'),
		'CL519' => __("EL MELON", 'woocommerce'),
		'CL475' => __("CASPANA", 'woocommerce'),
		'CL394' => __("GUAYACAN", 'woocommerce'),
		'CL578' => __("LAS BREAS", 'woocommerce'),
		'CL580' => __("LAS DICHAS", 'woocommerce'),
		'CL593' => __("LIQUINE", 'woocommerce'),
		'CL601' => __("LONGOVILO", 'woocommerce'),
		'CL51' => __("VINA DEL MAR", 'woocommerce'),
		'CL365' => __("CAJON DEL MAIPO", 'woocommerce'),
		'CL436' => __("AGUA BUENA", 'woocommerce'),
		'CL456' => __("CACHAGUA", 'woocommerce'),
		'CL489' => __("CHOLGUAN", 'woocommerce'),
		'CL495' => __("COIHUE", 'woocommerce'),
		'CL502' => __("COZ COZ", 'woocommerce'),
		'CL515' => __("EL CARMEN RENGO", 'woocommerce'),
		'CL514' => __("EL BELLOTO", 'woocommerce'),
		'CL528' => __("ENSENADA", 'woocommerce'),
		'CL533' => __("GABRIELA MISTRAL", 'woocommerce'),
		'CL546' => __("HUAMALATA", 'woocommerce'),
		'CL560' => __("LA CEBADA", 'woocommerce'),
		'CL474' => __("CARRIZAL BAJO", 'woocommerce'),
		'CL574' => __("LAGUNILLAS", 'woocommerce'),
		'CL586' => __("LAS VENTANAS", 'woocommerce'),
		'CL598' => __("LLIFEN", 'woocommerce'),
		'CL607' => __("LOS MAITENES", 'woocommerce'),
		'CL602' => __("LONTUE", 'woocommerce'),
		'CL627' => __("MONTE AGUILA", 'woocommerce'),
		'CL632' => __("NAHUELTORO", 'woocommerce'),
		'CL649' => __("PARCELA EL CARMEN", 'woocommerce'),
		'CL659' => __("PICHIDANGUI", 'woocommerce'),
		'CL637' => __("NIREHUAO", 'woocommerce'),
		'CL712' => __("RININAHUE", 'woocommerce'),
		'CL732' => __("SOCOS", 'woocommerce'),
		'CL170' => __("NACIMIENTO", 'woocommerce'),
		'CL382' => __("VALDIVIA DE PAINE", 'woocommerce'),
		'CL216' => __("TEODORO SCHMIDT", 'woocommerce'),
		'CL596' => __("LLANOS DE GUANTA", 'woocommerce'),
		'CL527' => __("EL TANGUE", 'woocommerce'),
		'CL613' => __("MAMINA", 'woocommerce'),
		'CL144' => __("VILLA ALEGRE", 'woocommerce'),
		'CL32' => __("ANDACOLLO", 'woocommerce'),
		'CL537' => __("GUANGUALI", 'woocommerce'),
		'CL577' => __("LARMAHUE", 'woocommerce'),
		'CL749' => __("VALLE NEVADO", 'woocommerce'),
		'CL721' => __("SAN MANUEL", 'woocommerce'),
		'CL751' => __("VEGAS DE ITATA", 'woocommerce'),
		'CL750' => __("VALLE SIMPSON", 'woocommerce'),
		'CL752' => __("VILLA ALHUE", 'woocommerce'),
		'CL754' => __("VILLA CERRO CASTILLO", 'woocommerce'),
		'CL755' => __("VILLA MANIHUALES", 'woocommerce'),
		'CL757' => __("VILLA ORTEGA", 'woocommerce'),
		'CL626' => __("MOLINOS", 'woocommerce'),
		'CL630' => __("MONTENEGRO", 'woocommerce'),
		'CL653' => __("PELEQUEN", 'woocommerce'),
		'CL741' => __("TOTORALILLO", 'woocommerce'),
		'CL307' => __("LA REINA", 'woocommerce'),
		'CL266' => __("PUYEHUE", 'woocommerce'),
		'CL421' => __("MAITENCILLO", 'woocommerce'),
		'CL678' => __("PUERTO DOMINGUEZ", 'woocommerce'),
		'CL688' => __("PUNTA LAVAPIE", 'woocommerce'),
		'CL697' => __("QUILIMARI", 'woocommerce'),
		'CL747' => __("TUBUL", 'woocommerce'),
		'CL195' => __("SAN FABIAN", 'woocommerce'),
		'CL153' => __("SAN PEDRO DE LA PAZ", 'woocommerce'),
		'CL569' => __("LA RUFINA", 'woocommerce'),
		'CL261' => __("QUEMCHI", 'woocommerce'),
		'CL257' => __("DALCAHUE", 'woocommerce'),
		'CL184' => __("CHILLAN VIEJO", 'woocommerce'),
		'CL753' => __("VILLA AMENGUAL", 'woocommerce'),
		'CL715' => __("RUCA RAQUI (SAAVEDRA)", 'woocommerce'),
		'CL123' => __("RIO CLARO", 'woocommerce'),
		'CL251' => __("MAULLIN", 'woocommerce'),
		'CL14' => __("SIERRA GORDA", 'woocommerce'),
		'CL764' => __("FUERTE BAQUEDANO", 'woocommerce'),
		'CL126' => __("CAUQUENES", 'woocommerce'),
		'CL317' => __("PROVIDENCIA", 'woocommerce'),
		'CL211' => __("PADRE LAS CASAS", 'woocommerce'),
		'CL328' => __("PIRQUE", 'woocommerce'),
		'CL623' => __("MINCHA", 'woocommerce'),
		'CL624' => __("MININCO", 'woocommerce'),
		'CL327' => __("PUENTE ALTO", 'woocommerce'),
		'CL326' => __("VITACURA", 'woocommerce'),
		'CL296' => __("CERRILLOS", 'woocommerce'),
		'CL339' => __("CURACAVI", 'woocommerce'),
		'CL291' => __("PRIMAVERA", 'woocommerce'),
		'CL478' => __("CAYUCUPIL", 'woocommerce'),
		'CL606' => __("LOS LINGUES", 'woocommerce'),
		'CL639' => __("NUEVA ALDEA", 'woocommerce'),
		'CL699' => __("QUINCHAMALI", 'woocommerce'),
		'CL633' => __("NAL", 'woocommerce'),
		'CL616' => __("MARBELLA", 'woocommerce'),
		'CL357' => __("OLIVAR ALTO", 'woocommerce'),
		'CL619' => __("MECHAICO", 'woocommerce'),
		'CL397' => __("POLPAICO", 'woocommerce'),
		'CL690' => __("PUYUHUAPI", 'woocommerce'),
		'CL680' => __("PUERTO TRANQUILO", 'woocommerce'),
		'CL681' => __("PUERTO VELERO", 'woocommerce'),
		'CL684' => __("PUNTA CORONA", 'woocommerce'),
		'CL666' => __("PORTILLO", 'woocommerce'),
		'CL683' => __("PUNTA COLORADA", 'woocommerce'),
		'CL692' => __("QUELON", 'woocommerce'),
		'CL685' => __("PUNTA DE PARRA", 'woocommerce'),
		'CL686' => __("PUNTA DE TRALCA", 'woocommerce'),
		'CL687' => __("PUNTA DELGADA", 'woocommerce'),
		'CL689' => __("PUNUCAPA", 'woocommerce'),
		'CL728' => __("SECTOR LA PENA", 'woocommerce'),
		'CL691' => __("QUEBRADA DE TALCA", 'woocommerce'),
		'CL693' => __("QUEPE", 'woocommerce'),
		'CL670' => __("PUELO", 'woocommerce'),
		'CL694' => __("QUETALMAHUE", 'woocommerce'),
		'CL319' => __("QUILICURA", 'woocommerce'),
		'CL695' => __("QUEULE", 'woocommerce'),
		'CL696' => __("QUILCHE", 'woocommerce'),
		'CL698' => __("QUILLAGUA", 'woocommerce'),
		'CL700' => __("QUINTAY", 'woocommerce'),
		'CL702' => __("RAFAEL", 'woocommerce'),
		'CL703' => __("RAMADILLAS", 'woocommerce'),
		'CL704' => __("RANGUELMO", 'woocommerce'),
		'CL725' => __("SAN SEBASTIAN", 'woocommerce'),
		'CL726' => __("SANTA CLARA", 'woocommerce'),
		'CL275' => __("LAGO VERDE", 'woocommerce'),
		'CL727' => __("SANTA ROSA DE CHENA", 'woocommerce'),
		'CL729' => __("SEWELL", 'woocommerce'),
		'CL555' => __("ISLA NEGRA", 'woocommerce'),
		'CL674' => __("PUERTO AGUIRRE", 'woocommerce'),
		'CL742' => __("TREGUALEMU", 'woocommerce'),
		'CL743' => __("TREHUACO", 'woocommerce'),
		'CL744' => __("TROVOLHUE", 'woocommerce'),
		'CL745' => __("TRUMAO", 'woocommerce'),
		'CL717' => __("SALADILLO", 'woocommerce'),
		'CL746' => __("TRUPAN", 'woocommerce'),
		'CL748' => __("TULAHUEN", 'woocommerce'),
		'CL720' => __("SAN JULIAN", 'woocommerce'),
		'CL718' => __("SAN ALFONSO", 'woocommerce'),
		'CL762' => __("YUSTE", 'woocommerce'),
		'CL223' => __("CURACAUTIN", 'woocommerce'),
		'CL231' => __("VICTORIA", 'woocommerce'),
		'CL348' => __("EL SALVADOR", 'woocommerce'),
		'CL374' => __("LOMAS DE LO AGUIRRE", 'woocommerce'),
		'CL493' => __("CODELCO RADOMIRO TOMIC", 'woocommerce'),
		'CL512' => __("DOMEYKO", 'woocommerce'),
		'CL540' => __("HACIENDA LOS ANDES", 'woocommerce'),
		'CL30' => __("LA SERENA", 'woocommerce'),
		'CL395' => __("JUNCAL", 'woocommerce'),
		'CL612' => __("MALALCAHUELLO", 'woocommerce'),
		'CL417' => __("RAUQUEN", 'woocommerce'),
		'CL335' => __("CALERA DE TANGO", 'woocommerce'),
		'CL210' => __("NUEVA IMPERIAL", 'woocommerce'),
		'CL213' => __("PITRUFQUEN", 'woocommerce'),
		'CL43' => __("PUNITAQUI", 'woocommerce'),
		'CL458' => __("CAIMANES", 'woocommerce'),
		'CL583' => __("LAS RAMADAS DE TULAHUEN", 'woocommerce'),
		'CL427' => __("CABO DE HORNOS", 'woocommerce'),
		'CL314' => __("NUNOA", 'woocommerce'),
		'CL324' => __("SAN MIGUEL", 'woocommerce'),
		'CL312' => __("MACUL", 'woocommerce'),
		'CL315' => __("PEDRO AGUIRRE CERDA", 'woocommerce'),
		'CL333' => __("SAN BERNARDO", 'woocommerce'),
		'CL316' => __("PENALOLEN", 'woocommerce'),
		'CL304' => __("LA FLORIDA", 'woocommerce'),
		'CL298' => __("CONCHALI", 'woocommerce'),
		'CL302' => __("INDEPENDENCIA", 'woocommerce'),
		'CL299' => __("EL BOSQUE", 'woocommerce'),
		'CL323' => __("SAN JOAQUIN", 'woocommerce'),
		'CL321' => __("RECOLETA", 'woocommerce'),
		'CL320' => __("QUINTA NORMAL", 'woocommerce'),
		'CL297' => __("CERRO NAVIA", 'woocommerce'),
		'CL322' => __("RENCA", 'woocommerce'),
		'CL305' => __("LA GRANJA", 'woocommerce'),
		'CL300' => __("ESTACION CENTRAL", 'woocommerce'),
		'CL306' => __("LA PINTANA", 'woocommerce'),
		'CL325' => __("SAN RAMON", 'woocommerce'),
		'CL301' => __("HUECHURABA", 'woocommerce'),
		'CL311' => __("LO PRADO", 'woocommerce'),
		'CL313' => __("MAIPU", 'woocommerce'),
		'CL303' => __("LA CISTERNA", 'woocommerce'),
		'CL308' => __("LAS CONDES", 'woocommerce'),
		'CL295' => __("SANTIAGO CENTRO", 'woocommerce'),
	);
	
	return $states;
}
add_filter('woocommerce_checkout_fields' , 'cambio_campos_checkout', 9999);
function cambio_campos_checkout( $fields ) {
	
	$fields['billing']['billing_state']['label']= 'Comunas'; 
	$fields['shipping']['shipping_state']['label']= 'Comunas'; 

	unset($fields['billing']['billing_postcode']);
	unset($fields['shipping']['shipping_postcode']);
	
	
	return $fields;
}
add_filter('woocommerce_get_country_locale', 'wc_change_state_label_locale');
function wc_change_state_label_locale($locale){
    $locale['CL']['state']['label'] = __('Comunas', 'woocommerce');
    return $locale;
}
add_action( 'wp_head', 'shipit_woocommerce_tip' );
function shipit_woocommerce_tip(){
	?>
	<script type="text/javascript">
	jQuery( document ).ready(function( $ ) {
		jQuery('label[for="billing_state"]').text('Comunas');
		$('#billing_state').change(function(){
			jQuery('body').trigger('update_checkout');
		});
	});
	</script>
	<?php
}
?>