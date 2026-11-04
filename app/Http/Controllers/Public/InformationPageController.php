<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InformationPageController extends Controller
{
    private const PAGES = [
        'about' => [
            'title' => 'A wider view of investing',
            'intro' => 'A considered way to explore investment categories and follow documented account activity.',
            'sections' => [
                ['title' => 'Built for clarity', 'body' => 'The platform is being developed to connect opportunity information, legal documents, verified account activity, and investor reporting in one place.'],
                ['title' => 'No implied ownership', 'body' => 'A company, product, brand, property, or digital asset appearing as a category does not mean that an investment or ownership interest is available. Any future offer must describe its legal structure and supporting rights.'],
                ['title' => 'Designed to grow carefully', 'body' => 'International availability depends on legal review, investor eligibility, identity controls, and service providers in each jurisdiction.'],
            ],
        ],
        'how-it-works' => [
            'title' => 'How the platform is intended to work',
            'intro' => 'A transparent flow from account setup to documented records.',
            'sections' => [
                ['title' => '1. Create and verify an account', 'body' => 'Investors create an account, verify their email, and complete any identity and eligibility checks required for the relevant market.'],
                ['title' => '2. Review a documented offer', 'body' => 'An opportunity is displayed only after review of its legal structure, risk disclosures, terms, and investor-facing documentation.'],
                ['title' => '3. Fund through an approved provider', 'body' => 'If funding is enabled in a jurisdiction, the payment method, network, amount, fees, and status are recorded and independently reconciled.'],
                ['title' => '4. Track verified activity', 'body' => 'Account balances are derived from posted ledger records. Distributions and valuations require documented source data; no return is assumed or guaranteed.'],
            ],
        ],
        'payment-methods' => [
            'title' => 'Payment methods',
            'intro' => 'No deposits, withdrawals, or cryptocurrency transfers are currently enabled.',
            'sections' => [
                ['title' => 'Before payments go live', 'body' => 'The platform must select an eligible payment provider, complete legal and compliance review, configure transaction monitoring, and test reconciliation and withdrawal controls.'],
                ['title' => 'Cryptocurrency networks', 'body' => 'Any future cryptocurrency deposit flow must show the exact asset and network, verify destination and amount, independently confirm on-chain transactions, prevent duplicate credits, and never request private keys or seed phrases.'],
                ['title' => 'Current status', 'body' => 'This page describes planned safeguards only. It does not provide a wallet address and does not accept payment.'],
            ],
        ],
        'faq' => [
            'title' => 'Frequently asked questions',
            'intro' => 'Straight answers about Commonwealth and current service availability.',
            'sections' => [
                ['title' => 'Can I invest today?', 'body' => 'No. Live investment subscriptions and payment operations are disabled. Availability will be updated only after the required legal, compliance, and provider approvals are complete.'],
                ['title' => 'Are the listed brands or companies available as investments?', 'body' => 'Category references do not imply that any company, brand, product, intellectual property, or security has offered an investment. A future listing would require documented legal rights and review.'],
                ['title' => 'Are returns guaranteed?', 'body' => 'No. No return is guaranteed. An investment may lose value or all invested capital.'],
                ['title' => 'How are balances calculated?', 'body' => 'When posted account activity exists, balances are calculated from ledger entries. Sample balances are not displayed.'],
                ['title' => 'Which countries are supported?', 'body' => 'No jurisdiction is currently approved for live investing. Availability will be published only after country-specific legal and operational review.'],
            ],
        ],
        'terms' => [
            'title' => 'Terms and conditions — working draft',
            'draft' => true,
            'intro' => 'This draft describes the current development site only and is not a final agreement for any live investment service.',
            'sections' => [
                ['title' => 'Platform status', 'body' => 'The platform is under development. It does not currently accept investment orders, deposits, withdrawals, or custody instructions.'],
                ['title' => 'No offer or advice', 'body' => 'Site content is general information, not an offer, solicitation, recommendation, or personalized investment advice.'],
                ['title' => 'Risk', 'body' => 'Investments involve risk and may result in loss of some or all capital. Terms for any future offering will be set out in its governing documents.'],
                ['title' => 'Review required', 'body' => 'These terms must be reviewed by qualified counsel for each intended jurisdiction before public launch or live operations.'],
            ],
        ],
        'privacy' => [
            'title' => 'Privacy notice — working draft',
            'draft' => true,
            'intro' => 'This draft describes intended data handling for the development platform and must be completed before launch.',
            'sections' => [
                ['title' => 'Data collected', 'body' => 'The application may process account identity and contact details, authentication records, investor profile data, support inquiries, and transaction records when those features are enabled.'],
                ['title' => 'Use and protection', 'body' => 'Data is used to provide account access, security, support, compliance checks, and auditability. Sensitive profile fields are encrypted; uploaded identity and legal documents must use private storage with access checks.'],
                ['title' => 'Sharing and retention', 'body' => 'No provider or retention schedule is claimed here. The final notice must identify service providers, legal bases, cross-border transfers, retention periods, user rights, and contact details.'],
                ['title' => 'Review required', 'body' => 'A privacy professional must complete this notice for the jurisdictions in which the service will operate.'],
            ],
        ],
        'risk-disclosure' => [
            'title' => 'Investment risk disclosure',
            'intro' => 'This general summary is not a substitute for the specific risk factors in an offering’s governing documents.',
            'sections' => [
                ['title' => 'Loss of capital', 'body' => 'Investment values can decline. Investors may lose some or all of the amount invested.'],
                ['title' => 'Liquidity and valuation', 'body' => 'Private business, property, and alternative investments can be difficult to value or sell. Withdrawals may be limited by legal terms, market conditions, or available liquidity.'],
                ['title' => 'Digital assets', 'body' => 'Digital assets can be volatile and are exposed to technology, cybersecurity, network, custody, regulatory, and counterparty risks.'],
                ['title' => 'No projected performance', 'body' => 'The platform does not publish fabricated or hypothetical returns as actual results. Historical or projected figures, if ever shown, must be sourced, labelled, and accompanied by applicable disclosures.'],
            ],
        ],
        'aml-kyc' => [
            'title' => 'AML and identity verification — working draft',
            'draft' => true,
            'intro' => 'This page is a development-stage outline, not a complete compliance program.',
            'sections' => [
                ['title' => 'Identity and eligibility', 'body' => 'Before any live service, account verification, beneficial ownership checks where relevant, investor eligibility, and jurisdiction restrictions must be defined and operational.'],
                ['title' => 'Screening and monitoring', 'body' => 'A live program requires approved sanctions and AML screening, risk-based transaction monitoring, escalation, record keeping, and suspicious activity procedures.'],
                ['title' => 'No live transactions', 'body' => 'Deposits, withdrawals, custody, and investment subscriptions are currently disabled.'],
                ['title' => 'Review required', 'body' => 'Qualified compliance counsel and service providers must approve the controls for each operating jurisdiction before launch.'],
            ],
        ],
    ];

    public function show(string $page): View
    {
        if (! isset(self::PAGES[$page])) {
            throw new NotFoundHttpException();
        }

        return view('info.show', [
            'page' => self::PAGES[$page],
            'slug' => $page,
        ]);
    }
}