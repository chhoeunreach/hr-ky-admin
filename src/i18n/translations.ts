export type AppLanguage = 'en' | 'km';

export interface TranslationDictionary {
  appName: string;
  tagline: string;
  teamLeaders: string;
  bestStaff: string;
  serviceAwards: string;
  courseDiplomas: string;
  digitalHr: string;
  batchGenerator: string;
  exportCertificate: string;
  exportWindowsLaravel: string;
  languageToggle: string;
  presets: string;
  content: string;
  design: string;
  seals: string;
  signatures: string;
  certificateTitle: string;
  subtitle: string;
  recipientName: string;
  recipientTitle: string;
  achievementTitle: string;
  citation: string;
  metrics: string;
  issueDate: string;
  expiryDate: string;
  credentialId: string;
  preview: string;
  downloadPdf: string;
  downloadPng: string;
  print: string;
  languageMode: string;
  bilingualMode: string;
  khmerOnlyMode: string;
  englishOnlyMode: string;
  khmerTitle: string;
  khmerSubtitle: string;
  khmerRecipientName: string;
  khmerAchievementTitle: string;
  khmerCitation: string;
  institutionalPreamble: string;
  certifiedHours: string;
  employeeRegistryId: string;
  complianceTrack: string;
  officialSeal: string;
  goldShimmer: string;
  windowsPackage: string;
  laravelIntegration: string;
  downloadFullProject: string;
  mergeToProject: string;
}

export const TRANSLATIONS: Record<AppLanguage, TranslationDictionary> = {
  en: {
    appName: 'CertiCraft Studio',
    tagline: 'Professional Certificate & KNEAYERNG HRMS Credential Generator',
    teamLeaders: 'Team Leaders',
    bestStaff: 'Best of Staff',
    serviceAwards: 'Service Awards',
    courseDiplomas: 'Training & Development (CPD)',
    digitalHr: 'KNEAYERNG HRMS (Hours)',
    batchGenerator: 'Batch Generator',
    exportCertificate: 'Export Certificate',
    exportWindowsLaravel: 'Merge / Export Project',
    downloadFullProject: 'Download Full Project (.zip)',
    mergeToProject: 'Merge to hr-ky-admin',
    languageToggle: 'ភាសាខ្មែរ',
    presets: 'Layout Models & Presets',
    content: 'Certificate Fields',
    design: 'Design & Frame',
    seals: 'Seals & Signatures',
    signatures: 'Signatures',
    certificateTitle: 'Certificate Title',
    subtitle: 'Subtitle Tagline',
    recipientName: 'Recipient Name',
    recipientTitle: 'Recipient Title / Honorific',
    achievementTitle: 'Course or Achievement Title',
    citation: 'Commendation Citation',
    metrics: 'Distinction Metrics',
    issueDate: 'Date Issued',
    expiryDate: 'Validity / Expiry',
    credentialId: 'Credential ID',
    preview: 'Certificate Preview',
    downloadPdf: 'Download Vector PDF (A4)',
    downloadPng: 'Download High-Res PNG',
    print: 'Print Certificate',
    languageMode: 'Certificate Language',
    bilingualMode: 'Bilingual (Khmer + English)',
    khmerOnlyMode: 'Khmer Only (ភាសាខ្មែរ)',
    englishOnlyMode: 'English Only',
    khmerTitle: 'Khmer Certificate Title',
    khmerSubtitle: 'Khmer Subtitle / Decree',
    khmerRecipientName: 'Khmer Recipient Name',
    khmerAchievementTitle: 'Khmer Course / Award Title',
    khmerCitation: 'Khmer Citation / Commendation',
    institutionalPreamble: 'Institutional Authority Preamble',
    certifiedHours: 'KNEAYERNG HRMS Hours Certified',
    employeeRegistryId: 'Employee Registry ID',
    complianceTrack: 'HR Competency Track',
    officialSeal: 'Official 3D Seal',
    goldShimmer: 'Gold Foil Shimmer',
    windowsPackage: 'Windows Package (.bat)',
    laravelIntegration: 'Laravel 10/11 Integration',
  },
  km: {
    appName: 'CertiCraft Studio',
    tagline: 'ប្រព័ន្ធបង្កើតវិញ្ញាបនបត្រ និងម៉ោងបណ្តុះបណ្តាលឌីជីថល HR',
    teamLeaders: 'មេដឹកនាំក្រុម',
    bestStaff: 'បុគ្គលិកឆ្នើម',
    serviceAwards: 'សេវាកម្មឆ្នើម',
    courseDiplomas: 'វគ្គបណ្តុះបណ្តាល (CPD)',
    digitalHr: 'ឌីជីថល HR (ម៉ោង)',
    batchGenerator: 'បង្កើតជាក្រុម (Batch)',
    exportCertificate: 'ទាញយកវិញ្ញាបនបត្រ',
    exportWindowsLaravel: 'តភ្ជាប់ ឬនាំចេញគម្រោង',
    downloadFullProject: 'ទាញយកគម្រោងពេញ (.zip)',
    mergeToProject: 'ភ្ជាប់ទៅ hr-ky-admin',
    languageToggle: 'English',
    presets: 'គំរូ Layout & Presets',
    content: 'ទិន្នន័យវិញ្ញាបនបត្រ',
    design: 'រចនាបថ & ស៊ុម',
    seals: 'ត្រា & ហត្ថលេខា',
    signatures: 'ហត្ថលេខាផ្លូវការ',
    certificateTitle: 'ចំណងជើងវិញ្ញាបនបត្រ',
    subtitle: 'អនុចំណងជើង / សេចក្តីប្រកាស',
    recipientName: 'ឈ្មោះជ័យលាភី (អង់គ្លេស)',
    recipientTitle: 'គោរមងារ / តួនាទី',
    achievementTitle: 'ចំណងជើងវគ្គសិក្សា ឬសមិទ្ធផល',
    citation: 'សេចក្តីថ្លែងអំណរគុណ និងសរសើរ',
    metrics: 'សូចនាករវាយតម្លៃ',
    issueDate: 'កាលបរិច្ឆេទចេញ',
    expiryDate: 'សុពលភាព',
    credentialId: 'លេខសម្គាល់វិញ្ញាបនបត្រ',
    preview: 'មើលគំរូជាមុន',
    downloadPdf: 'ទាញយកឯកសារ PDF (A4)',
    downloadPng: 'ទាញយករូបភាព PNG ច្បាស់',
    print: 'បោះពុម្ពវិញ្ញាបនបត្រ',
    languageMode: 'ភាសាវិញ្ញាបនបត្រ',
    bilingualMode: 'ទ្វេភាសា (ខ្មែរ + អង់គ្លេស)',
    khmerOnlyMode: 'ភាសាខ្មែរតែមួយគត់',
    englishOnlyMode: 'ភាសាអង់គ្លេសតែមួយគត់',
    khmerTitle: 'ចំណងជើងជាភាសាខ្មែរ',
    khmerSubtitle: 'សេចក្តីផ្តើមជាភាសាខ្មែរ',
    khmerRecipientName: 'ឈ្មោះជ័យលាភីជាភាសាខ្មែរ',
    khmerAchievementTitle: 'វគ្គសិក្សា / សមិទ្ធផលជាភាសាខ្មែរ',
    khmerCitation: 'អត្ថន័យសរសើរជាភាសាខ្មែរ',
    institutionalPreamble: 'សេចក្តីប្រកាស ឬសេចក្តីសម្រេចស្ថាប័ន',
    certifiedHours: 'ម៉ោងបណ្តុះបណ្តាលឌីជីថលទទួលស្គាល់',
    employeeRegistryId: 'លេខកូដបុគ្គលិក / ជ័យលាភី',
    complianceTrack: 'ជំនាញ និងបទដ្ឋានធនធានមនុស្ស',
    officialSeal: 'ត្រាមាស 3D ផ្លូវការ',
    goldShimmer: 'ពន្លឺចាំងពណ៌មាស',
    windowsPackage: 'កញ្ចប់ Windows (.bat)',
    laravelIntegration: 'ការតភ្ជាប់ជាមួយ Laravel 10/11',
  },
};
