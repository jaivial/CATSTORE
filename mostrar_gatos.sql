-- Script SQL para mostrar todos los registros de la tabla animal

USE gatos;

-- Mostrar todos los registros
SELECT 
    id,
    nombre,
    tipo,
    color,
    CASE 
        WHEN sexo = 1 THEN 'Macho'
        WHEN sexo = 0 THEN 'Hembra'
        ELSE 'No especificado'
    END AS sexo,
    precio,
    CASE 
        WHEN foto IS NOT NULL THEN 'Disponible'
        ELSE 'No disponible'
    END AS foto
FROM 
    animal
ORDER BY 
    id ASC;

-- Para ver toda la información, incluyendo datos binarios de la foto
-- SELECT * FROM animal; 