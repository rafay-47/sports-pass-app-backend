<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create a function to update sport service count
        DB::unprepared('
            CREATE OR REPLACE FUNCTION update_sport_service_count()
            RETURNS TRIGGER AS $$
            BEGIN
                IF TG_OP = \'INSERT\' THEN
                    UPDATE sports 
                    SET number_of_services = (
                        SELECT COUNT(*) 
                        FROM sport_services 
                        WHERE sport_id = NEW.sport_id
                    )
                    WHERE id = NEW.sport_id;
                    RETURN NEW;
                ELSIF TG_OP = \'DELETE\' THEN
                    UPDATE sports 
                    SET number_of_services = (
                        SELECT COUNT(*) 
                        FROM sport_services 
                        WHERE sport_id = OLD.sport_id
                    )
                    WHERE id = OLD.sport_id;
                    RETURN OLD;
                ELSIF TG_OP = \'UPDATE\' THEN
                    -- Update count for old sport if sport_id changed
                    IF OLD.sport_id != NEW.sport_id THEN
                        UPDATE sports 
                        SET number_of_services = (
                            SELECT COUNT(*) 
                            FROM sport_services 
                            WHERE sport_id = OLD.sport_id
                        )
                        WHERE id = OLD.sport_id;
                    END IF;
                    
                    -- Update count for new sport
                    UPDATE sports 
                    SET number_of_services = (
                        SELECT COUNT(*) 
                        FROM sport_services 
                        WHERE sport_id = NEW.sport_id
                    )
                    WHERE id = NEW.sport_id;
                    RETURN NEW;
                END IF;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Create triggers
        DB::unprepared('
            CREATE TRIGGER sport_service_count_trigger
            AFTER INSERT OR UPDATE OR DELETE ON sport_services
            FOR EACH ROW EXECUTE FUNCTION update_sport_service_count();
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop triggers
        DB::unprepared('DROP TRIGGER IF EXISTS sport_service_count_trigger ON sport_services;');
        
        // Drop function
        DB::unprepared('DROP FUNCTION IF EXISTS update_sport_service_count();');
    }
};
