<?php
/**
 * Migration Script - Recreate and seed missing dim_film and dim_staff tables.
 */
require_once __DIR__ . '/database.php';

try {
    $db = Database::getConnection();
    echo "Connected to database successfully.\n";

    // 1. Create dim_film if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS public.dim_film (
        film_id integer NOT NULL PRIMARY KEY,
        film_title character varying(255) NOT NULL,
        film_category character varying(50) NOT NULL,
        film_rating character varying(10) NOT NULL
    )");
    echo "Table 'dim_film' verified/created.\n";

    // 2. Create dim_staff if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS public.dim_staff (
        staff_id integer NOT NULL PRIMARY KEY,
        staff_name character varying(100) NOT NULL,
        staff_email character varying(100),
        store_id integer,
        active boolean NOT NULL DEFAULT true
    )");
    echo "Table 'dim_staff' verified/created.\n";

    // 3. Clear existing values to avoid key collisions
    $db->exec("TRUNCATE TABLE public.dim_film CASCADE");
    $db->exec("TRUNCATE TABLE public.dim_staff CASCADE");

    // 4. Seed dim_staff
    $stmtStaff = $db->prepare("INSERT INTO public.dim_staff (staff_id, staff_name, staff_email, store_id, active) VALUES (:id, :name, :email, :store, :active)");
    $stmtStaff->execute(['id' => 1, 'name' => 'Mike Hillyer', 'email' => 'Mike.Hillyer@sakilastaff.com', 'store' => 1, 'active' => true]);
    $stmtStaff->execute(['id' => 2, 'name' => 'Jon Stephens', 'email' => 'Jon.Stephens@sakilastaff.com', 'store' => 2, 'active' => true]);
    echo "dim_staff seeded.\n";

    // 5. Seed dim_film with deterministic mock data matching the Sakila range (1 to 1000)
    $adjectives = ["ACADEMY", "ACE", "ADAPTATION", "AFFAIR", "AGENT", "AIRPLANE", "AIRPORT", "ALABAMA", "ALADDIN", "ALASKA", "ALIEN", "ALLEY", "ALLIGATOR", "ALONE", "ALTER", "AMADEUS", "AMELIE", "AMERICAN", "AMISTAD", "ANACONDA", "ANALYZE", "ANGELS", "ANIMAL", "ANNE", "ANONYMOUS", "ANTITRUST", "APACHE", "APOCALYPSE", "APOLLO", "ARABIA", "ARACHNOPHOBIA", "ARENA", "ARIZONA", "ARMAGEDDON", "ARTIST", "ATTACKS", "ATTRACTION", "AUTUMN", "BABY", "BACK", "BAD", "BAKED", "BALLOON", "BANANAS", "BANDITS", "BANG", "BARBARELLA", "BAREFOOT", "BASIC", "BEACH", "BEAST", "BEAUTY", "BED", "BEETHOVEN", "BEHAVIOR", "BEHIND", "BELLY", "BENSON", "BETRAYED", "BEVERLY", "BEYOND", "BICYCLE", "BIG", "BILLION", "BINGO", "BIOGRAPHY", "BIRCH", "BIRD", "BIRDMAN", "BIRDS", "BLACK", "BLADE", "BLANKET", "BLIND", "BLOOD", "BLUES", "BODY", "BOILED", "BOMBER", "BOOGIE", "BORN", "BORROWED", "BOSS", "BOTTLE", "BOULEVARD", "BOUND", "BOWFINGER", "BOY", "BOYS", "BRAVE", "BREAKING", "BRIDE", "BRIDGE", "BRIGHT", "BROTHERHOOD", "BULL", "BUNCH", "BURNING", "BUTCH", "BUTTERFLY", "CABIN", "CADDYSHACK", "CALENDAR", "CALIFORNIA", "CAMPUS", "CANDIDATE", "CANDLES", "CANYON", "CAPER", "CAR", "CARE", "CARIBBEAN", "CAROL", "CARPET", "CASABLANCA", "CASINO", "CAT", "CATCH", "CATALOG", "CAUSE", "CAVALCADE", "CELEBRITY", "CENTER", "CHAINS", "CHAMBER", "CHAMPION", "CHANCE", "CHAPPAQUIDDICK", "CHARADE", "CHARIOTS", "CHASE", "CHEAPER", "CHICAGO", "CHICKEN", "CHILDREN", "CHILL", "CHINATOWN", "CHOCOLAT", "CHOCOLATE", "CHOPPER", "CHORUS", "CHUNKY", "CID", "CINCINNATI", "CIRCUS", "CITIZEN", "CLAN", "CLASS", "CLASH", "CLAY", "CLEOPATRA", "CLERKS", "CLINT", "CLOCKWORK", "CLONES", "CLOSER", "CLUB", "CLUES", "COAL", "COAST", "COCOON", "COLD", "COLLEGE", "COLOR", "COMANCHEROS", "COMMAND", "COMMANDMENTS", "COUPLES", "CRAZY", "CREATURES", "CREEPY", "CRIMSON", "CROOKED", "CROSSING", "CROW", "CROWD", "CRUELTY", "CRUSADE", "CRY", "CUPBOARD", "CURTAIN", "CYRANO", "DADDY", "DANCES", "DANCING", "DANGER", "DARES", "DARK", "DARLING", "DATE", "DAUGHTER", "DAWN", "DAY", "DAYS", "DEAD", "DEATH", "DECISION", "DEEP", "DEER", "DELIVERANCE", "DESERT", "DESPERATE", "DESTINATION", "DETECTIVE", "DEVIL", "DIARY", "DIRTY", "DISC", "DISAPPEAR", "DISTANT", "DIVIDE", "DIVINE", "DIVORCE", "DOC", "DOCTOR", "DOG", "DOGMA", "DONUT", "DOOM", "DOORS", "DOUBLE", "DOWNHILL", "DRACULA", "DRAGON", "DREAM", "DRIFTER", "DRIVER", "DROWNING", "DRUM", "DUCK", "DURHAM", "DUST", "DYING", "EAGLES", "EARLY", "EARTH", "EAST", "EASY", "ED", "EDGE", "EDWARD", "EGG", "EGYPT", "EIGHT", "ELEPHANT", "ELEMENT", "ELIZABETH", "EMPIRE", "ENCOUNTERS", "END", "ENEMY", "ENOUGH", "ENTRAPMENT", "ESCAPE", "EVE", "EVERYONE", "EVIL", "EXCITEMENT", "EXORCIST", "EXPEDITION", "EXTREME", "EYES", "FABULOUS", "FACE", "FALCON", "FAMILY", "FANG", "FANTASIA", "FAR", "FAREWELL", "FAST", "FATAL", "FATHER", "FEATHER", "FELLOWSHIP", "Fever", "FICTION", "FIGHT", "FILMS", "FINDING", "FIRE", "FIREBALL", "FIRST", "FISH", "FLAGS", "FLATLINERS", "FLIGHT", "FLINTSTONES", "FLOWERS", "FLY", "FOOLISH", "FORREST", "FORWARD", "FRIDAY", "FRIENDS", "FRONT", "FROST", "FUGITIVE", "FULL", "GALAXY", "GAMES", "GANDHI", "GANG", "GARDEN", "GASLIGHT", "GATE", "GENTLEMEN", "GHOST", "GIANT", "GIGLI", "GILBERT", "GLADIATOR", "GLASS", "GLEN GARRY", "GLORY", "GO", "GODFATHER", "GOLD", "GOLDFINGER", "GOOD", "GOODFELLAS", "GORGEOUS", "GOSPEL", "GRACELAND", "GRADUATE", "GRAFFITI", "GRAPES", "GRAVE", "GREASE", "GREAT", "GREATEST", "GREEN", "GROOVE", "GROUNDHOG", "GUMP", "GUN", "GUNFIGHT", "GUNS", "GUYS", "HALF", "HALL", "HALLOWEEN", "HAMLET", "HAND", "Hanging", "HANOVER", "HAPPY", "HARBOR", "HARD", "HAROLD", "HARRY", "HAUNTING", "HAWK", "HAWKS", "HEART", "HEARTS", "HEAVEN", "HEAVENLY", "HEAVY", "HEDWIG", "HELLFIGHTERS", "HIGH", "HIGHWAY", "HILL", "HILLS", "HIM", "HISTORY", "HOMICIDE", "HONEY", "HOOK", "HOPE", "HORN", "HORROR", "HOT", "HOTEL", "HOUSE", "HOUSES", "HUG", "HUMAN", "HUNCHBACK", "HUNTER", "HURRICANE", "HUSTLER", "ICE", "IDAHO", "IDENTITY", "IDOLS", "ILLUSION", "IMAGINARY", "IMPACT", "IMPOSSIBLE", "INCH", "INDEPENDENCE", "INDIAN", "INDIANA", "INFORMER", "INGREDIENTS", "INNER", "INNOCENT", "INSIDER", "INTELLIGENCE", "INTERVIEW", "INVASION", "ISLAND", "ITALIAN", "JACKET", "JADE", "JAPANESE", "JASON", "JAWS", "JEDI", "JEEPERS", "JERK", "JERRY", "JESUS", "JEOPARDY", "JESSICA", "JEWELL", "JIDD", "JIMI", "JINGLE", "JOON", "JUDGEMENT", "JUGGLER", "JULIET", "JUMPING", "JUNGLE", "JURASSIC", "KANE", "KARATE", "KENTUCKY", "KEY", "KID", "KILL", "KILLER", "KILLING", "KINGS", "KISMET", "KISS", "KISSING", "KNOCK", "KRAMER", "LADY", "LADYBUGS", "LAGOON", "LANCELOT", "LAND", "LANGUAGE", "LARAMIE", "LAST", "LAW", "LAWRENCE", "LEAGUE", "LEATHERNECKS", "LEGAL", "LEGEND", "LIAISONS", "LIBERTY", "LIES", "LIFE", "LIGHT", "LIGHTS", "LIMIT", "LION", "LOATHING", "LOBSTER", "LOCK", "LOLA", "LONELY", "LONG", "LOOK", "LOOP", "LOOSE", "LORD", "LORDS", "LOST", "LOUISIANA", "LOVE", "LOVELY", "LOVER", "LOVERS", "LUCK", "LUCKY", "LUST", "MADIBA", "MADIGAN", "MADNESS", "MAGIC", "MAGNIFICENT", "MAGNOLIA", "MAIDEN", "MAJESTIC", "MAKE", "MALL", "MAN", "MANCHURIAN", "MANDELA", "MAP", "MARRIED", "MARY", "MASK", "MASKED", "MASSACRE", "MATCH", "MATRIX", "MAUDE", "MAXIMUM", "MAZE", "MEET", "MEETING", "MEMENTO", "MEN", "METROPOLIS", "MIDSUMMER", "MIDNIGHT", "MINDS", "MINORITY", "MIRACLE", "MISSION", "MOCKINGBIRD", "MODERN", "MONEY", "MONSOON", "MONSTER", "MONTEREY", "MONTANA", "MONTY", "MOON", "MOONSHINE", "MORN", "MORNING", "MOTORCYCLE", "MOUNDS", "MOUNT", "MOVIE", "MULHOLLAND", "MUMMY", "MURDER", "MUSCLE", "MUSIC", "MUSKETEERS", "MUTE", "MY", "MYSTIC", "NAME", "NASH", "NATIONAL", "NATURAL", "NEIGHBORS", "NELSON", "NETWORK", "NEVER", "NEW", "NEWTON", "NIGHT", "NIGHTMARE", "NO", "NORTH", "NOTHING", "NOTTING", "NOW", "OCTOBER", "OFFICE", "OKLAHOMA", "ON", "ONCE", "ONE", "ONES", "OPERA", "OPPOSITE", "ORANGE", "ORDER", "ORDINARY", "OSCAR", "OTHER", "OTHERS", "OUT", "OUTBREAK", "OUTLAW", "OUTRAGEOUS", "OVER", "PACIFIC", "PADRE", "PAIN", "PAINTED", "PANIC", "PARADISE", "PARIS", "PARK", "PARTY", "PASSAGE", "PASSION", "PATRIOT", "PEACH", "PEARL", "PELICAN", "PEOPLE", "PERFECT", "PERFORMANCE", "PERSONAL", "PET", "PHANTOM", "PHILADELPHIA", "PIANIST", "PICKPOT", "PICTURE", "PIE", "PINOCCHIO", "PIRATES", "PITTSBURGH", "PLACES", "PLAIN", "PLAN", "PLANET", "PLAY", "PLATOON", "PLEASANT", "POCKET", "POISON", "POLICE", "POLISH", "POOL", "POPULAR", "POST", "POTTER", "PREJUDICE", "PRESIDENT", "PRIDE", "PRIMA", "PRIMARY", "PRINCESS", "PRISON", "PRIVATE", "PRODIGY", "PROGRAM", "PULP", "PUNCH", "PURE", "PURPLE", "PYRAMID", "QUEEN", "QUEST", "QUIET", "RABBIT", "RAGING", "RAIDERS", "RAIN", "RAINBOW", "RANCH", "RANDOM", "RANGE", "RASCALS", "RATING", "RAW", "REAR", "REBEL", "REBOUND", "RECREATION", "RED", "REDEMPTION", "REFORM", "RELATION", "REMEMBER", "RENT", "RESERVOIR", "REST", "RESURRECTION", "RETURN", "REVOLUTION", "RIDER", "RIDERS", "RIDGE", "RIGHT", "RING", "RINGS", "RIOT", "RIVER", "ROAD", "ROBBERY", "ROBIN", "ROCK", "ROCKY", "ROOF", "ROOM", "ROSE", "ROUGE", "ROUND", "ROYAL", "RUDE", "RULE", "RULES", "RUN", "RUNAWAY", "RUNNER", "RUSH", "RUSTLIN", "SABRINA", "SADDLE", "SAGEBRUSH", "SAILOR", "SAINT", "SALUTE", "SAMURAI", "SAN", "SAND", "SASSY", "SATURDAY", "SAVING", "SCALAWAG", "SCENT", "SCHOOL", "SCORPION", "SCOOBY", "SCREEN", "SEA", "SEABISCUIT", "SEARCH", "SEATTLE", "SECRET", "SECRETS", "SENSE", "SENSITIVITY", "SEVEN", "SHADOW", "SHAKESPEARE", "SHANE", "SHARK", "SHAWSHANK", "SHE COAL", "SHINING", "SHIP", "SHOCK", "SHOOT", "SHOOTIST", "SHOW", "SHREK", "SIDE", "SIEGE", "SIGN", "SILENCE", "SILVERADO", "SIMON", "SINCERE", "SINGLE", "SISTER", "SISTERS", "SKY", "SLEEPING", "SLEEPLESS", "SLING", "SLUMS", "SMILE", "SMILES", "SMOKING", "SNAKES", "SNEAKERS", "SNOOPY", "SNOWMAN", "SOAP", "SOLDIERS", "SOMETHING", "SONG", "SONS", "SOUP", "SOUTH", "SPARTACUS", "SPEED", "SPICE", "SPIES", "SPIN", "SPIRIT", "SPLENDOR", "SPOIL", "SPOTTED", "SPY", "STAGE", "STAGECOACH", "STALLION", "STAND", "STAR", "STARS", "STATE", "STEEL", "STEERS", "STING", "STORM", "STORY", "STRANGERS", "STREETCAR", "STRICTLY", "SUBMARINE", "SUGAR", "SUMMER", "SUN", "SUNDANCE", "SUNRISE", "SUNSET", "SUPER", "SURVIVORS", "SUSPECTS", "SWEET", "SWEETS", "SWEDISH", "TAXI", "TEEN", "TEMPLE", "TEN", "TEXAS", "THEORY", "THIEF", "THINGS", "TIGHT", "TIMBERLAND", "TIME", "TITAN", "TITANS", "TO", "TOMORROW", "TOO", "TOOTSIE", "TOP", "TORQUE", "TOUR", "TOWERS", "TOWN", "TRACY", "TRAFFIC", "TRAGEDY", "TRAIN", "TRAINS", "TRAMP", "TRANSLATION", "TRAP", "TREASURE", "TREATMENT", "TREES", "TRIAL", "TRIP", "TROUBLE", "TRUMAN", "TURN", "TUXEDO", "TWELVE", "TWO", "TYCOON", "UGLY", "UNCLE", "UNDEFEATED", "UNDER", "UNDERWORLD", "UNFORGIVEN", "UNITED", "UNTOUCHABLES", "UP", "UPTOWN", "USUAL", "VACATION", "VALENTINE", "VALLEY", "VANISHED", "VEGAS", "VELVET", "VERTIGO", "VICTORY", "VIDEOS", "VIETNAM", "VILLAGE", "VIRGIN", "VIRGINIA", "VOYAGE", "WAGON", "WAIT", "WAKING", "WALL", "WANDERING", "WAR", "WARDROBE", "WARRIORS", "WASTELAND", "WATCH", "WATERFRONT", "WATSON", "WAVE", "WAY", "WEDDING", "WEEKEND", "WEST", "WESTWARD", "WHALE", "WHAT", "WHEELS", "WHEN", "WHISPERER", "WILD", "WILL", "WIND", "WINDOW", "WINDWARD", "WINNER", "WINNING", "WISE", "WITCHES", "WITHOUT", "WIZARD", "WOLVES", "WOMEN", "WONDER", "WONDERFUL", "WORD", "WORDS", "WORK", "WORKING", "WORLD", "WORST", "WRATH", "WRITTEN", "WRONG", "WYOMING", "YENTL", "YOUTH", "ZEUS", "ZOOM"];
    
    $categories = ["Action", "Animation", "Children", "Classics", "Comedy", "Documentary", "Drama", "Family", "Foreign", "Horror", "Music", "New", "Sci-Fi", "Sports", "Travel", "Games"];
    $ratings = ["G", "PG", "PG-13", "R", "NC-17"];

    $db->beginTransaction();
    $stmtFilm = $db->prepare("INSERT INTO public.dim_film (film_id, film_title, film_category, film_rating) VALUES (:id, :title, :category, :rating)");

    for ($i = 1; $i <= 1000; $i++) {
        $adj1 = $adjectives[($i * 7) % count($adjectives)];
        $adj2 = $adjectives[($i * 13) % count($adjectives)];
        $title = $adj1 . " " . $adj2;
        if ($adj1 === $adj2) {
            $title = $adj1 . " MOVIE";
        }
        $cat = $categories[($i * 17) % count($categories)];
        $rating = $ratings[($i * 23) % count($ratings)];

        $stmtFilm->execute([
            'id' => $i,
            'title' => $title,
            'category' => $cat,
            'rating' => $rating
        ]);
    }
    $db->commit();
    echo "1000 dim_film rows successfully generated and seeded!\n";
    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "Error running migration: " . $e->getMessage() . "\n";
    exit(1);
}
