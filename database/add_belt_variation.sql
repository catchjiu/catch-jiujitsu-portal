-- Add belt variation column for kids belts
-- Kids belts (Grey, Yellow, Orange, Green) have 3 variations:
-- 'white' = belt color with white bar (stripes on white)
-- 'solid' = solid belt color (no bar)
-- 'black' = belt color with black bar (stripes on black)

ALTER TABLE users ADD COLUMN belt_variation ENUM('white', 'solid', 'black') NULL DEFAULT NULL AFTER rank;

-- Set default for existing kids belt users to 'white'
UPDATE users SET belt_variation = 'white' WHERE rank IN ('Grey', 'Yellow', 'Orange', 'Green');
