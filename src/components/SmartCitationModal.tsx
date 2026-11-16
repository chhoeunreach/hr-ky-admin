import React, { useState } from 'react';
import { Sparkles, X, Check, RefreshCw } from 'lucide-react';
import { AwardCategory } from '../types/certificate';

interface SmartCitationModalProps {
  isOpen: boolean;
  onClose: () => void;
  category: AwardCategory;
  recipientName: string;
  courseOrAchievement: string;
  onApplyCitation: (citation: string, title?: string) => void;
}

export const SmartCitationModal: React.FC<SmartCitationModalProps> = ({
  isOpen,
  onClose,
  category,
  recipientName,
  courseOrAchievement,
  onApplyCitation,
}) => {
  const [selectedCategory, setSelectedCategory] = useState<AwardCategory>(category || 'service');
  const [tone, setTone] = useState<'prestigious' | 'inspiring' | 'warm' | 'academic'>('prestigious');
  const [customKeyPoints, setCustomKeyPoints] = useState('');
  const [generatedOptions, setGeneratedOptions] = useState<string[]>([]);
  const [isGenerating, setIsGenerating] = useState(false);

  if (!isOpen) return null;

  const handleGenerate = () => {
    setIsGenerating(true);
    setTimeout(() => {
      const name = recipientName.trim() || 'the recipient';
      let options: string[] = [];

      if (selectedCategory === 'staff') {
        if (tone === 'prestigious') {
          options = [
            `For extraordinary work ethic, peerless professionalism, consistently exceeding organizational KPIs, and setting an enduring benchmark for operational excellence.`,
            `Conferred in high recognition of outstanding performance, exemplary integrity, and tireless dedication to the core mission and values of our organization.`,
            `In solemn tribute to exceptional diligence, flawless execution, and embodying the highest standards of staff distinction throughout the year.`
          ];
        } else if (tone === 'warm') {
          options = [
            `With heartfelt gratitude for your relentless optimism, your willingness to always step up for the team, and making our workplace a brighter, more productive home for everyone.`,
            `Celebrating your dependability, positive spirit, and stellar contributions that inspire colleagues and fuel our shared success every single day.`,
            `In proud recognition of being the true backbone of our organization, bringing passion, warmth, and excellence to everything you do.`
          ];
        } else {
          options = [
            `Awarded to ${name} for outstanding contributions, delivering high-impact solutions with speed and quality, and championing true teamwork under the spirit of "គ្នា​យើង".`,
            `For remarkable productivity, proactive initiative, and maintaining a flawless track record of excellence across all department goals.`,
            `In recognition of exceptional talent, personal initiative, and setting the standard for what it means to be an exemplary colleague and high achiever.`
          ];
        }
      } else if (selectedCategory === 'service') {
        if (tone === 'prestigious') {
          options = [
            `For consistently demonstrating exceptional customer empathy, upholding an unprecedented 99.6% client satisfaction benchmark, and elevating organization-wide service standards through tireless dedication.`,
            `In recognition of distinguished excellence in frontline service delivery, resolving complex customer inquiries with poise, and establishing lasting customer trust through exemplary care.`,
            `Conferred for peerless commitment to client delight, rapid resolution metrics, and transforming everyday service interactions into memorable brand advocacy.`
          ];
        } else if (tone === 'warm') {
          options = [
            `With heartfelt gratitude for bringing warmth, patience, and kindness to every customer interaction, making our clients feel truly valued and heard every single day.`,
            `In sincere appreciation for going the extra mile, turning stressful customer moments into delightful experiences with boundless optimism and care.`,
            `Celebrating your genuine passion for helping people, your bright energy, and your stellar record of delivering five-star support with heart.`
          ];
        } else {
          options = [
            `Awarded to ${name} for setting the gold standard in proactive customer engagement, achieving industry-leading NPS scores, and inspiring team-wide service excellence.`,
            `For outstanding dedication to customer care, exceptional first-contact resolution agility, and championing the voice of the customer across all departments.`,
            `In recognition of remarkable resilience, empathetic troubleshooting, and an uncompromising dedication to delivering world-class customer service.`
          ];
        }
      } else if (selectedCategory === 'leader') {
        if (tone === 'prestigious') {
          options = [
            `For extraordinary executive leadership, orchestrating high-stakes organizational milestones with strategic clarity, and cultivating an enduring culture of operational excellence.`,
            `In high tribute to visionary leadership, fostering cross-functional alignment, and guiding the team to surpass critical annual performance goals with integrity.`,
            `Conferred for distinguished stewardship, exceptional crisis management, and empowering multi-disciplinary squads to achieve transformative business outcomes.`
          ];
        } else if (tone === 'inspiring') {
          options = [
            `For being the fearless spark that inspires the team to push boundaries, celebrate bold experiments, and achieve what once seemed impossible with infectious enthusiasm.`,
            `In honor of a truly servant leader whose relentless belief in team potential unlocks peak performance and cultivates tomorrow's industry leaders.`,
            `For leading from the front with courage, kindness, and visionary optimism, transforming group challenges into extraordinary team triumphs.`
          ];
        } else {
          options = [
            `Awarded to ${name} for cultivating high-trust team dynamics, championing psychological safety, and elevating collective velocity through compassionate mentorship.`,
            `In recognition of stellar team development, reducing turnover to zero, and building an inclusive environment where every contributor flourishes.`,
            `For balanced, outcome-driven leadership that harmonizes ambitious delivery timelines with team wellbeing and sustained professional growth.`
          ];
        }
      } else {
        // Online Course
        if (tone === 'academic') {
          options = [
            `Having successfully demonstrated mastery in theoretical foundations and practical implementations through comprehensive coursework, rigorous capstone reviews, and examination with honors.`,
            `In recognition of academic distinction, intellectual rigor, and verified competency across all advanced curriculum modules and professional competency benchmarks.`,
            `For fulfilling all statutory requirements and practical assessments with superior marks, establishing validated expertise in the discipline.`
          ];
        } else {
          options = [
            `For successfully mastering modern applied skills, completing hands-on architectural projects with distinction, and demonstrating exemplary dedication to lifelong learning.`,
            `Conferred upon ${name} in recognition of exceptional technical proficiency, creative problem solving, and outstanding capstone project execution.`,
            `Having satisfied all curriculum milestones with distinction, demonstrating high aptitude, persistent curiosity, and mastery of core domain competencies.`
          ];
        }
      }

      // If user supplied custom key points, blend them in
      if (customKeyPoints.trim()) {
        options = options.map((opt) => `${opt} Special commendation for: ${customKeyPoints.trim()}.`);
      }

      setGeneratedOptions(options);
      setIsGenerating(false);
    }, 250);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
      <div className="w-full max-w-xl bg-white dark:bg-slate-900 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-slate-800">
          <div className="flex items-center gap-2.5">
            <div className="p-2 bg-amber-50 dark:bg-amber-950/40 text-amber-600 rounded-lg">
              <Sparkles className="w-5 h-5" />
            </div>
            <div>
              <h3 className="text-base font-semibold text-slate-900 dark:text-slate-100">
                Citation & Praise Generator
              </h3>
              <p className="text-xs text-slate-500 dark:text-slate-400">
                Craft glowing, polished award wording tailored to your recipient
              </p>
            </div>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-md transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Form Body */}
        <div className="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
          {/* Category Selector */}
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
              Award Discipline
            </label>
            <div className="grid grid-cols-4 gap-2">
              <button
                type="button"
                onClick={() => setSelectedCategory('staff')}
                className={`px-2 py-2 text-xs font-medium rounded-lg border transition-all text-center cursor-pointer ${
                  selectedCategory === 'staff'
                    ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                    : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'
                }`}
              >
                Best of Staff
              </button>
              <button
                type="button"
                onClick={() => setSelectedCategory('leader')}
                className={`px-2 py-2 text-xs font-medium rounded-lg border transition-all text-center cursor-pointer ${
                  selectedCategory === 'leader'
                    ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                    : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'
                }`}
              >
                Team Leader
              </button>
              <button
                type="button"
                onClick={() => setSelectedCategory('service')}
                className={`px-2 py-2 text-xs font-medium rounded-lg border transition-all text-center cursor-pointer ${
                  selectedCategory === 'service'
                    ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                    : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'
                }`}
              >
                Service
              </button>
              <button
                type="button"
                onClick={() => setSelectedCategory('course')}
                className={`px-2 py-2 text-xs font-medium rounded-lg border transition-all text-center cursor-pointer ${
                  selectedCategory === 'course'
                    ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 shadow-xs font-bold'
                    : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'
                }`}
              >
                Course
              </button>
            </div>
          </div>

          {/* Tone Selector */}
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-2">
              Citation Tone
            </label>
            <div className="grid grid-cols-4 gap-2">
              {(['prestigious', 'inspiring', 'warm', 'academic'] as const).map((t) => (
                <button
                  key={t}
                  type="button"
                  onClick={() => setTone(t)}
                  className={`px-2.5 py-1.5 text-xs font-medium rounded-lg border capitalize transition-all cursor-pointer ${
                    tone === t
                      ? 'border-slate-800 dark:border-slate-200 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900'
                      : 'border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800'
                  }`}
                >
                  {t}
                </button>
              ))}
            </div>
          </div>

          {/* Custom Accomplishments / Key Points */}
          <div>
            <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400 mb-1.5">
              Specific Achievements or Metrics (Optional)
            </label>
            <input
              type="text"
              placeholder="e.g. 99.8% CSAT score, zero escalations, mentored 12 junior specialists"
              value={customKeyPoints}
              onChange={(e) => setCustomKeyPoints(e.target.value)}
              className="w-full px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-amber-500"
            />
          </div>

          {/* Generate Button */}
          <button
            type="button"
            onClick={handleGenerate}
            disabled={isGenerating}
            className="w-full flex items-center justify-center gap-2 py-2.5 px-4 text-xs font-semibold text-white bg-slate-900 dark:bg-slate-100 dark:text-slate-900 rounded-lg hover:bg-slate-800 dark:hover:bg-slate-200 transition-colors cursor-pointer"
          >
            {isGenerating ? (
              <RefreshCw className="w-4 h-4 animate-spin" />
            ) : (
              <Sparkles className="w-4 h-4" />
            )}
            <span>Generate Suggested Citations</span>
          </button>

          {/* Generated Results */}
          {generatedOptions.length > 0 && (
            <div className="space-y-3 pt-2">
              <label className="block text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">
                Choose an Option
              </label>
              {generatedOptions.map((opt, i) => (
                <div
                  key={i}
                  className="p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-lg text-xs leading-relaxed text-slate-800 dark:text-slate-200 flex flex-col gap-2.5"
                >
                  <p className="italic font-serif text-[13px]">{opt}</p>
                  <div className="flex justify-end">
                    <button
                      type="button"
                      onClick={() => {
                        onApplyCitation(opt);
                        onClose();
                      }}
                      className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-amber-700 dark:text-amber-300 bg-amber-100/70 dark:bg-amber-950/40 hover:bg-amber-200/70 rounded-md transition-colors cursor-pointer"
                    >
                      <Check className="w-3.5 h-3.5" />
                      <span>Use This Citation</span>
                    </button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </div>
      </div>
    </div>
  );
};
