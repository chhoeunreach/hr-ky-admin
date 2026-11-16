<?php
copy('certicraft/src/CertificateStudio.tsx', 'certicraft/src/modules/certificates/CertificateStudio.tsx');
copy('certicraft/src/components/Header.tsx', 'certicraft/src/modules/certificates/components/Header.tsx');
copy('certicraft/src/i18n/translations.ts', 'certicraft/src/modules/certificates/i18n/translations.ts');
echo "Synced to modules successfully!\n";
