#!/bin/bash

# Pokretanje migracija
echo "Pokrećem migracije..."
php Backend/migrations/migrate.php
if [ $? -ne 0 ]; then
  echo "Greška prilikom pokretanja migracija. Zaustavljam skriptu."
  exit 1
fi

# Pokretanje PHP backend servera
echo "Pokrećem backend server na http://localhost:8000..."
php -S localhost:8000 -t Backend &
if [ $? -ne 0 ]; then
  echo "Greška prilikom pokretanja backend servera. Zaustavljam skriptu."
  exit 1
fi

# Pokretanje Angular frontend servera
echo "Pokrećem Angular frontend na http://localhost:4200..."
cd Frontend
ng serve --open
if [ $? -ne 0 ]; then
  echo "Greška prilikom pokretanja frontend servera. Zaustavljam skriptu."
  exit 1
fi
