export type AwardCategory = 'staff' | 'leader' | 'service' | 'course' | 'custom';

export type CertificateOrientation = 'landscape' | 'portrait';

export type CertificateTheme =
  | 'regal-gold'      // Imperial gold & deep navy, ornate borders, classic serif
  | 'knea-yoeng-gold' // Warm gold ring & deep espresso plum matching the Knea Yoeng brand
  | 'navy-gold'       // Royal Navy (#0A224E) & Metallic Gold executive ribbon theme
  | 'modern-slate'    // Executive slate & emerald/teal accents, minimalist geometric
  | 'crimson-crest'   // Royal burgundy & warm bronze, prestigious executive
  | 'minimal-swiss'   // Modernist typography, sharp rules, asymmetric balance
  | 'tech-obsidian'   // Dark tech obsidian with cyan/amber cyber trim, crypto hash
  | 'sapphire-royal'; // Deep royal blue with filigree corners & double gold hairline

export type LayoutTemplateOption =
  | 'executive-verify'
  | 'executive-verify-v2'
  | 'executive-navy-ribbon'
  | 'classic'
  | 'executive-medal-ribbon'
  | 'executive-imperial-sash';
export type FontStyleOption = 'cinzel' | 'playfair' | 'cormorant' | 'sans' | 'khmer';
export type FieldFontFamily =
  | 'cinzel'
  | 'playfair'
  | 'cormorant'
  | 'sans'
  | 'montserrat'
  | 'alex-brush'
  | 'great-vibes'
  | 'khmer-moul'
  | 'khmer-sans'
  | 'khmer-battambang'
  | 'mono';

export type FieldTypographyKey =
  | 'issuerName'
  | 'issuerDepartment'
  | 'authorityPreamble'
  | 'khmerTitle'
  | 'certificateTitle'
  | 'bannerText'
  | 'subtitle'
  | 'khmerRecipientName'
  | 'recipientName'
  | 'organizationSubBanner'
  | 'courseOrAchievementTitle'
  | 'citation'
  | 'citationClosing'
  | 'khmerCitation'
  | 'issueDate'
  | 'signatoryName'
  | 'signatoryRole';

export interface FieldTypographyStyle {
  fontFamily?: FieldFontFamily;
  fontSize?: number;
  fontWeight?: 'normal' | 'medium' | 'semibold' | 'bold' | 'extrabold';
  italic?: boolean;
  color?: string;
  letterSpacing?: number; // px
  uppercase?: boolean;
}
export type SignatureStyleOption = 'alex-brush' | 'great-vibes' | 'font-signature' | 'drawn' | 'upload';
export type BorderStyleOption =
  | 'navy-gold-ribbon'
  | 'imperial-gold-wave-sash'
  | 'emerald-royal-ribbon'
  | 'crimson-imperial-ribbon'
  | 'gold-wave-scroll'
  | 'navy-gold-notched'
  | 'blue-guilloche-lace'
  | 'baroque-gold-wave'
  | 'blue-rosette-guilloche'
  | 'royal-geometric-arch'
  | 'khmer-kbach-lotus'
  | 'art-deco-gilded'
  | 'golden-ring-frame'
  | 'ornate-guilloche'
  | 'double-hairline'
  | 'corner-flourish'
  | 'minimal-box'
  | 'regal-ribbon'
  | 'imperial-corner-flourish'
  | 'vintage-engraving-frame'
  | 'modern-gradient-edge'
  | 'royal-filigree-scroll'
  | 'grand-palace-khmer'
  | 'executive-double-sash'
  | 'none';
export type LogoOrnamentStyle =
  | 'classic-laurel'
  | 'laurel-crossed-wreath'
  | 'crown-laurel-wreath'
  | 'three-star-laurel'
  | 'khmer-kbach'
  | 'rice-wheat-khmer'
  | 'royal-wings'
  | 'floral-baroque'
  | 'star-halo'
  | 'diamond-crest'
  | 'none';
export type DividerOrnamentStyle =
  | 'baroque-fleur'
  | 'acanthus-gold-line'
  | 'beaded-diamond-bar'
  | 'khmer-lotus'
  | 'khmer-angkor-crest'
  | 'royal-crown-line'
  | 'royal-plume'
  | 'diamond-stars'
  | 'double-scallop'
  | 'infinity-filigree'
  | 'minimal-gold-bar'
  | 'custom'
  | 'none';
export type BannerStyleOption =
  | 'gold-swallowtail'
  | 'navy-gold-ribbon'
  | 'crimson-gold-ribbon'
  | 'royal-blue-arch';
export type WatermarkStyleOption =
  | 'laurel'
  | 'crest'
  | 'academic'
  | 'star'
  | 'wave-mesh'
  | 'guilloche-rosette'
  | 'diagonal-brand'
  | 'rosette-lace';
export type SealStyleOption =
  | 'embossed-gold'
  | 'royal-ribbon'
  | 'crown-laurel'
  | 'navy-k-medal'
  | 'navy-laurel-crest'
  | 'gold-k-medallion'
  | 'gold-k-ribbon-seal'
  | 'wax-red-k'
  | 'laurel-wreath'
  | 'modern-star'
  | 'certified-stamp'
  | 'none';

export interface CertificateVisibility {
  showLogo?: boolean;
  showIssuerName?: boolean;
  showIssuerDepartment?: boolean;
  showHeaderDivider?: boolean;
  showAuthorityPreamble?: boolean;
  showKhmerTitle?: boolean;
  showEnglishTitle?: boolean;
  showTitleLaurels?: boolean;
  showBannerRibbon?: boolean;
  showSubtitle?: boolean;
  showKhmerRecipientName?: boolean;
  showEnglishRecipientName?: boolean;
  showRecipientDivider?: boolean;
  showOrganizationSubBanner?: boolean;
  showEmployeeIdBadge?: boolean;
  showAchievementTitle?: boolean;
  showKhmerCitation?: boolean;
  showEnglishCitation?: boolean;
  showCitationClosing?: boolean;
  showMetrics?: boolean;
  showHangingMedal?: boolean;
  showStorefrontWatermark?: boolean;
  showPreFooterDivider?: boolean;
  showIssueDate?: boolean;
  showCertificateId?: boolean;
  showSignatories?: boolean;
  showSecurityFooter?: boolean;
}

export interface Signatory {
  id: string;
  name: string;
  khmerName?: string;
  role: string;
  khmerRole?: string;
  organization?: string;
  signatureStyle: SignatureStyleOption;
  customSignatureData?: string; // Data URL if drawn or uploaded
  hidden?: boolean;
}

export interface MetricBadge {
  id: string;
  label: string;
  value: string;
  khmerLabel?: string;
}

export interface DigitalHrRecord {
  employeeId: string;
  department: string;
  khmerDepartment?: string;
  trainingHours: number; // e.g. 45 Digital Training Hours / CPD Units
  hrCompetencyTrack: string; // e.g. "Frontline Service Mastery", "Executive Team Leadership"
  khmerCompetencyTrack?: string;
  complianceStatus: 'Verified & Accredited' | 'Distinction / Honor Roll' | 'CPD Certified';
  managerName?: string;
  showDigitalHoursBadge: boolean;
}

export interface CertificateData {
  id: string;
  category: AwardCategory;
  orientation: CertificateOrientation;
  theme: CertificateTheme;
  layoutTemplate?: LayoutTemplateOption;
  
  // Dual-script Language mode
  languageMode?: 'bilingual' | 'khmer' | 'english';

  // Organization / Issuer
  issuer: {
    name: string;
    khmerName?: string;
    department: string;
    khmerDepartment?: string;
    logoType: 'crest' | 'academic' | 'laurel' | 'shield' | 'star' | 'knea-yoeng' | 'k-monogram' | 'custom';
    customLogoUrl?: string;
  };

  // Header & Title
  authorityPreamble?: string; // e.g. "BY THE AUTHORITY OF THE GOVERNING BOARD & EXECUTIVE COUNCIL"
  khmerPreamble?: string;     // e.g. "ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ"
  certificateTitle: string;
  khmerTitle?: string;        // e.g. "វិញ្ញាបនបត្រកិត្តិយស" or "លិខិតសរសើរមេដឹកនាំឆ្នើម"
  bannerText?: string;        // e.g. "THIS CERTIFICATE IS PRESENTED TO"
  subtitle: string;
  khmerSubtitle?: string;     // e.g. "ផ្តល់ជូនដើម្បីជាសក្ខីភាពបញ្ជាក់ថា"

  // Recipient
  recipientTitle?: string; // e.g. "To our valued team member", "Presented to"
  khmerRecipientTitle?: string;
  recipientName: string;
  khmerRecipientName?: string; // e.g. "ឈឿន រាជ"
  organizationSubBanner?: string; // e.g. "KNEAYERNG PHONE SHOP"
  recipientPhotoUrl?: string; // Data URL or path for recipient/staff profile portrait
  showRecipientPhoto?: boolean;
  recipientPhotoPosition?: 'top-right' | 'top-left' | 'beside-name';
  recipientPhotoShape?: 'circle' | 'rounded-portrait' | 'square';
  recipientPhotoSize?: number; // px (default 86)
  recipientPhotoZoom?: number; // 1.0 - 2.0
  recipientPhotoOffsetX?: number; // % 0 - 100 (default 50)
  recipientPhotoOffsetY?: number; // % 0 - 100 (default 25)
  recipientPhotoFrameOffsetX?: number; // px nudge on canvas (+ right, - left)
  recipientPhotoFrameOffsetY?: number; // px nudge on canvas (+ down, - up)
  employeeIdPrefix?: string; // e.g. "ID:" or ""
  
  // Core Citation & Context
  courseOrAchievementTitle: string;
  khmerCourseOrAchievementTitle?: string;
  citation: string;
  citationClosing?: string; // Second italicized closing sentence
  khmerCitation?: string;
  
  // Extra metrics (e.g., CSAT 99.4%, Distinction, 40 CPD Hours, Team Size 25)
  metrics: MetricBadge[];

  // KNEAYERNG HRMS & Training Hours Integration
  digitalHr: DigitalHrRecord;

  // Dates & Credentials
  issueDate: string;
  khmerIssueDate?: string;
  expiryDate?: string;
  credentialId: string;
  showQrCode: boolean;
  qrVerificationUrl: string;
  qrLabel?: string;       // Default "VERIFY CERTIFICATE"
  qrBadgeColor?: string;  // Pill badge background color
  qrSize?: number;        // px (default 64)

  // Hanging Medal Ornament (Top-Left Sash)
  hangingMedalStyle?: 'auto' | 'navy-sash' | 'gold-sash';
  hangingMedalScale?: number; // 0.7 - 1.3

  // Granular Element Visibility Flags (defaults to true when undefined)
  visibility?: CertificateVisibility;

  // Seal
  seal: {
    enabled: boolean;
    style: SealStyleOption;
    primaryText: string;
    secondaryText: string;
    year?: string;
    ribbonColor?: 'ruby' | 'navy' | 'emerald' | 'gold' | 'black';
  };

  // Signatures
  signatories: Signatory[];

  // Aesthetic adjustments
  styling: {
    primaryColor: string;
    accentColor: string;
    bgColor: string;
    secondaryTextColor?: string;
    signatureInkColor?: string;
    fontDisplay: FontStyleOption;
    fontBody: FontStyleOption;
    borderStyle: BorderStyleOption;
    borderWidth?: number; // 1 - 8 px
    borderInnerOffset?: number; // 0 - 24 px
    showCornerOrnaments?: boolean; // toggle 4-corner filigrees
    cornerOrnamentScale?: number; // 0.6 - 1.6
    borderPrimaryColor?: string;
    borderAccentColor?: string;
    dateBoxStyle?: 'stacked-card' | 'minimal' | 'inline';
    qrBoxStyle?: 'pill-banner' | 'clean' | 'badge';
    showWatermark: boolean;
    watermarkType: WatermarkStyleOption;
    watermarkOpacity: number;
    bannerStyle?: BannerStyleOption;
    recipientFontSize?: number; // 32 - 54 px
    titleFontSize?: number;     // 22 - 38 px
    logoScale?: number;         // 0.7 - 1.4
    showLaurelWreath?: boolean; // toggle victory laurel leaves on logo
    enableShimmer?: boolean;   // override luxury gold shimmer sweep
    logoOrnament?: LogoOrnamentStyle;
    dividerOrnament?: DividerOrnamentStyle;
    customDividerUrl?: string;
    dividerColor?: string;
    dividerWidth?: number;    // 140 - 460 px
    dividerScale?: number;    // 0.6 - 1.8
    dividerOffsetY?: number;  // -16 to 24 px
    dividerOpacity?: number;  // 0.2 - 1.0
    sealPosition?: 'bottom-center' | 'bottom-right' | 'bottom-left';
    fieldStyles?: Partial<Record<FieldTypographyKey, FieldTypographyStyle>>;
  };
}
