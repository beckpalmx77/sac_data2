-- SQL Optimization for Tires Plan Forecast Database Structure
-- Target Table: ims_tires_plan_forecast

-- 1. Create a composite index for the Excel import lookup process.
-- This query is executed for each row in the Excel sheet:
-- SELECT id FROM ims_tires_plan_forecast WHERE forcast_month = ? AND forcast_year = ? AND ar_code = ? AND sku_code = ? AND sale_name = ?
-- Creating this index prevents Full Table Scans (O(N)) and turns them into O(1)/O(log N) point lookups.
ALTER TABLE ims_tires_plan_forecast 
ADD INDEX idx_forecast_lookup (forcast_month, forcast_year, ar_code, sku_code, sale_name);

-- 2. Create an index on the month and year columns.
-- This is used for filtering data in general views and triggering sync operations (update historical sales data):
-- UPDATE ims_tires_plan_forecast p SET ... WHERE p.forcast_month = ? AND p.forcast_year = ?
ALTER TABLE ims_tires_plan_forecast 
ADD INDEX idx_month_year (forcast_month, forcast_year);
