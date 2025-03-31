#!/bin/bash

# Verificar que se proporciona el nombre del servidor
if [ "$#" -lt 1 ]; then
    echo "Uso: $0 <nombre_servidor>"
    exit 1
fi

# Obtener el nombre del servidor
NOMBRE_SERVIDOR=$1

# Mapeo de mods a sus URLs
declare -A MOD_URLS=(
    ["i3"]="https://github.com/mt-historical/i3"
    ["motorboat"]="https://github.com/APercy/motorboat"
    ["stamina"]="https://github.com/minetest-mods/stamina"
    ["i_have_hands"]="https://github.com/KingTheGuy/i_have_hands"
    ["animalia"]="https://github.com/ElCeejo/animalia"
    ["creatura"]="https://github.com/ElCeejo/creatura"
    ["hidename"]="https://github.com/AntumMT/mod-hidename"
    ["knives"]="https://github.com/Beanzilla/knives"
    ["toolranks"]="https://github.com/lisacvuk/minetest-toolranks"
)

# Obtener los mods seleccionados del formulario
MODS_SELECCIONADOS=(${@:2})

# Crear el directorio de mods y entrar en él
podman exec -it $NOMBRE_SERVIDOR bash -c "mkdir -p /var/lib/luanti/.minetest/mods && cd /var/lib/luanti/.minetest/mods"

# Instalar cada mod seleccionado
for mod in "${MODS_SELECCIONADOS[@]}"; do
    if [ -n "${MOD_URLS[$mod]}" ]; then
        echo "Instalando mod: $mod"
        podman exec -it $NOMBRE_SERVIDOR bash -c "cd /var/lib/luanti/.minetest/mods && git clone ${MOD_URLS[$mod]}"
        
        # Añadir el mod al world.mt
        podman exec -it $NOMBRE_SERVIDOR bash -c "cd /var/lib/luanti/.minetest/worlds/world && echo \"load_mod_$mod=true\" >> world.mt"
    fi
done

# Reiniciar el servidor
podman restart $NOMBRE_SERVIDOR 