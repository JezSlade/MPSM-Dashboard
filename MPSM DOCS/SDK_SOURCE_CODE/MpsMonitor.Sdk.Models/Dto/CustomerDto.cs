using System;
using System.Collections.Generic;
using System.Runtime.Serialization;

namespace MpsMonitor.Sdk.Models.Dto
{
    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class CustomerDto : CustomerBaseDto
    {
        /// <summary>
        /// Initializes a new instance of the <see cref="CustomerDto"/> class.
        /// </summary>
        public CustomerDto()
        {
            this.SdsCustomer = new SdsCustomerDto();
            this.CustomFieldValues = new List<CustomFieldValueDto>();
        }

        /// <summary>
        /// Gets or sets the delivery toner mails.
        /// </summary>
        /// <value>
        /// The delivery toner mails.
        /// </value>
        [DataMember]
        public string DeliveryTonerMails { get; set; }

        /// <summary>
        /// Gets or sets the contract expires.
        /// </summary>
        /// <value>
        /// The contract expires.
        /// </value>
        [DataMember]
        public DateTime? ContractExpires { get; set; }

        /// <summary>
        /// Gets or sets the telephone.
        /// </summary>
        /// <value>
        /// The telephone.
        /// </value>
        [DataMember]
        public string Telephone { get; set; }

        /* Questo è da mettere in un detail o una dashboard del cliente*/
        /// <summary>
        /// Gets or sets the monthly volume mono.
        /// </summary>
        /// <value>
        /// The monthly volume mono.
        /// </value>
        [DataMember]
        public int MonthlyVolumeMono { get; set; }

        /* Questo è da mettere in un detail o una dashboard del cliente*/
        /// <summary>
        /// Gets or sets the color of the monthly volume.
        /// </summary>
        /// <value>
        /// The color of the monthly volume.
        /// </value>
        [DataMember]
        public int MonthlyVolumeColor { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [enable massive shipping].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [enable massive shipping]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool EnableMassiveShipping { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [massive shipping send notification].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [massive shipping send notification]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool MassiveShippingSendNotification { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [massive shipping send logistic].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [massive shipping send logistic]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool MassiveShippingSendLogistic { get; set; }

        /// <summary>
        /// Gets or sets the vat number.
        /// </summary>
        /// <value>
        /// The vat number.
        /// </value>
        [DataMember]
        public string VatNumber { get; set; }

        /// <summary>
        /// Gets or sets the Fiscal Code.
        /// </summary>
        /// <value>
        /// The vat number.
        /// </value>
        [DataMember]
        public string FiscalCode { get; set; }

        /// <summary>
        /// Gets or sets the note.
        /// </summary>
        /// <value>
        /// The note.
        /// </value>
        [DataMember]
        public string Note { get; set; }

        /// <summary>
        /// Gets or sets the creation.
        /// </summary>
        /// <value>
        /// The creation.
        /// </value>
        [DataMember]
        public DateTime Creation { get; set; }

        /// <summary>
        /// Gets or sets the email.
        /// </summary>
        /// <value>
        /// The email.
        /// </value>
        [DataMember]
        public string Email { get; set; }

        /// <summary>
        /// Gets or sets the nominative.
        /// </summary>
        /// <value>
        /// The nominative.
        /// </value>
        [DataMember]
        public string Nominative { get; set; }

        /// <summary>
        /// Gets or sets the time zone.
        /// </summary>
        /// <value>
        /// The time zone.
        /// </value>
        [DataMember]
        public string TimeZone { get; set; }

        public string Language { get; set; }

        /// <summary>
        /// Gets or sets the short language.
        /// </summary>
        /// <value>
        /// The short language.
        /// </value>
        [DataMember]
        public string ShortLanguage { get; set; }

        /// <summary>
        /// Gets or sets the note for homepage.
        /// </summary>
        /// <value>
        /// The note for homepage.
        /// </value>
        [DataMember]
        public string NoteForHomepage { get; set; }

        /// <summary>
        /// Gets or sets the custom field values.
        /// </summary>
        /// <value>
        /// The custom field values.
        /// </value>
        [DataMember]
        public List<CustomFieldValueDto> CustomFieldValues { get; set; }

        /// <summary>
        /// Gets or sets the dealer.
        /// </summary>
        /// <value>
        /// The dealer.
        /// </value>
        [DataMember]
        public DealerBaseDto Dealer { get; set; }

        /// <summary>
        /// Gets or sets the time zone iana.
        /// </summary>
        /// <value>
        /// The time zone iana.
        /// </value>
        [DataMember]
        public string TimeZoneIana { get; set; }

        /// <summary>
        /// Gets or sets the jam enabled.
        /// </summary>
        /// <value>
        /// The jam enabled.
        /// </value>
        [DataMember]
        public bool? JamEnabled { get; set; }

        [DataMember]
        public bool JamIsEu { get; set; }

        /// <summary>
        /// Gets or sets the dealer jam enabled.
        /// </summary>
        /// <value>
        /// The dealer jam enabled.
        /// </value>
        [DataMember]
        public bool? DealerJamEnabled { get; set; }

        /// <summary>
        /// Gets or sets the ZZT contract number.
        /// </summary>
        /// <value>
        /// The ZZT contract number.
        /// </value>
        [DataMember]
        public string ZztContractNumber { get; set; }

        /// <summary>
        /// Gets or sets the print releaf identifier.
        /// </summary>
        /// <value>
        /// The print releaf identifier.
        /// </value>
        [DataMember]
        public string PrintReleafId { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is print releaf enable.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is print releaf enable; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsPrintReleafEnable { get; set; }

        /// <summary>
        /// Gets or sets the email explorer installation sent at.
        /// </summary>
        /// <value>
        /// The email explorer installation sent at.
        /// </value>
        [DataMember]
        public DateTime? EmailExplorerInstallationSentAt { get; set; }

        /// <summary>
        /// Get if a customer has SDS enabled
        /// </summary>
        [DataMember]
        public SdsCustomerDto SdsCustomer { get; set; }
    }


    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class CustomerListDto : CustomerBaseDto
    {
        /// <summary>
        /// Gets or sets the dealer identifier.
        /// </summary>
        /// <value>
        /// The dealer identifier.
        /// </value>
        [DataMember]
        public string DealerId { get; set; }

        /// <summary>
        /// Gets or sets the dealer code.
        /// </summary>
        /// <value>
        /// The dealer code.
        /// </value>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Gets or sets the dealer description.
        /// </summary>
        /// <value>
        /// The dealer description.
        /// </value>
        [DataMember]
        public string DealerDescription { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class CustomerBaseDto : EntityDto
    {
        /// <summary>
        /// Gets or sets the code.
        /// </summary>
        /// <value>
        /// The code.
        /// </value>
        [DataMember]
        public string Code { get; set; }

        /// <summary>
        /// Gets or sets the description.
        /// </summary>
        /// <value>
        /// The description.
        /// </value>
        [DataMember]
        // [JsonConverterAttribute(typeof(ObfuscatorStringConverter), CapabilitiesEnum.Customers_PrivacyData)]
        public string Description { get; set; }

        /// <summary>
        /// Gets or sets the country code.
        /// </summary>
        /// <value>
        /// The country code.
        /// </value>
        [DataMember]
        public string CountryCode { get; set; }

        /// <summary>
        /// Gets or sets the name of the country.
        /// </summary>
        /// <value>
        /// The name of the country.
        /// </value>
        [DataMember]
        public string CountryName { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether [country is eu].
        /// </summary>
        /// <value>
        ///   <c>true</c> if [country is eu]; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool CountryIsEu { get; set; }

        /// <summary>
        /// Gets or sets the external identifier.
        /// </summary>
        /// <value>
        /// The external identifier.
        /// </value>
        [DataMember]
        public string ExternalIdentifier { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class SdsCustomerDto
    {
        [DataMember]
        public bool IsEnabled { get; set; }

        [DataMember]
        public bool EnableRemoteEws { get; set; }

        [DataMember]
        public bool CSRFProtection { get; set; }

        [DataMember]
        public bool EnableDeviceAnnouncement { get; set; }
    }


    /// <summary>
    /// Create Customer dto
    /// </summary>
    [DataContract]
    public class CreateCustomerDto
    {
        /// <summary>
        /// Dealer code
        /// </summary>
        [DataMember]
        public string DealerCode { get; set; }

        /// <summary>
        /// Customer description
        /// </summary>
        [DataMember]
        public string CustomerDescription { get; set; }

        /// <summary>
        /// CustomerTimeZone
        /// </summary>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// SendExplorerInstallationInvitation
        /// </summary>
        [DataMember]
        public bool SendExplorerInstallationInvitation { get; set; }

        /// <summary>
        /// Customer mail address
        /// </summary>
        [DataMember]
        public string MailAddress { get; set; }

        /// <summary>
        /// Gets or sets the telephone.
        /// </summary>
        /// <value>
        /// The telephone.
        /// </value>
        [DataMember]
        public string Telephone { get; set; }

        /// <summary>
        /// Gets or sets the nominative.
        /// </summary>
        /// <value>
        /// The nominative.
        /// </value>
        [DataMember]
        public string Nominative { get; set; }

        /// <summary>
        /// Gets or sets the dealer customer infos.
        /// </summary>
        [DataMember]
        public string NoteForHomepage { get; set; }

        /// <summary>
        /// Gets or sets the customer notes.
        /// </summary>
        [DataMember]
        public string Note { get; set; }

        /// <summary>
        /// UseDealerDefaultProjectSettings
        /// </summary>
        [DataMember]
        public bool UseDealerDefaultProjectSettings { get; set; }

        /// <summary>
        /// UseDealerBillBookingSettings
        /// </summary>
        [DataMember]
        public bool UseDealerBillBookingSettings { get; set; }

        /// <summary>
        /// ProjectSCFirstDate
        /// </summary>
        [DataMember]
        public DateTime? ProjectSCFirstDate { get; set; }

        /// <summary>
        /// EnableSDS
        /// </summary>
        [DataMember]
        public bool EnableSDS { get; set; }

        /// <summary>
        /// Gets or sets the external identifier.
        /// </summary>
        /// <value>
        /// The external identifier.
        /// </value>
        [DataMember]
        public string ExternalIdentifier { get; set; }


        /// <summary>
        /// Internal Set
        /// </summary>
        public string CreatorAccountEmail { get; set; }

        /// <summary>
        /// Gets or sets the country code.
        /// </summary>
        /// <value>
        /// The country code.
        /// </value>
        [DataMember]
        public string CountryCode { get; set; }

        /// <summary>
        /// Sets the custom field values.
        /// </summary>
        /// <value>
        /// The custom field values.
        /// </value>
        [DataMember]
        public List<CustomFieldValueDto> CustomFieldValues { get; set; }
    }

    /// <summary>
    /// 
    /// </summary>
    [DataContract]
    public class UpdateCustomerDto
    {
        /// <summary>
        /// Customer Code
        /// </summary>
        [DataMember]
        public string CustomerCode { get; set; }

        /// <summary>
        /// Customer Description
        /// </summary>
        [DataMember]
        public string CustomerDescription { get; set; }

        /// <summary>
        /// Vat Number
        /// </summary>
        [DataMember]
        public string VatNumber { get; set; }

        /// <summary>
        /// Fiscal Code
        /// </summary>
        [DataMember]
        public string FiscalCode { get; set; }

        /// <summary>
        /// TrialEndAt
        /// </summary>
        [DataMember]
        public DateTime? TrialEndAt { get; set; }

        /// <summary>
        /// Is Toner Enabled
        /// </summary>
        [DataMember]
        [Obsolete("Use /Customer/AlertSettings/Update to update that field")]
        public bool? IsTonerEnabled { get; set; }

        /// <summary>
        /// Is Photo Enabled
        /// </summary>
        [DataMember]
        [Obsolete("Use /Customer/AlertSettings/Update to update that field")]
        public bool? IsPhotoEnabled { get; set; }

        /// <summary>
        /// Is Maintenance Kit Enabled
        /// </summary>
        [DataMember]
        [Obsolete("Use /Customer/AlertSettings/Update to update that field")]
        public bool? IsMaintKitEnabled { get; set; }

        /// <summary>
        /// Is Waste Toner Box Enabled
        /// </summary>
        [DataMember]
        [Obsolete("Use /Customer/AlertSettings/Update to update that field")]
        public bool? IsWasteTonerBoxEnabled { get; set; }

        /// <summary>
        /// Is Transfer Kit Enabled
        /// </summary>
        [DataMember]
        [Obsolete("Use /Customer/AlertSettings/Update to update that field")]
        public bool? IsTransferKitEnabled { get; set; }

        /// <summary>
        /// CustomerTimeZone
        /// </summary>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// Gets or sets the country code.
        /// </summary>
        [DataMember]
        public string CountryCode { get; set; }

        /// <summary>
        /// Nominative
        /// </summary>
        [DataMember]
        public string Nominative { get; set; }

        /// <summary>
        /// EmailAddress
        /// </summary>
        [DataMember]
        public string EmailAddress { get; set; }

        /// <summary>
        /// Telephone
        /// </summary>
        [DataMember]
        public string Telephone { get; set; }

        /// <summary>
        /// Dealer Notes For Homepage
        /// </summary>
        [DataMember]
        public string NoteForHomepage { get; set; }

        /// <summary>
        /// Customer notes
        /// </summary>
        [DataMember]
        public string Note { get; set; }

        /// <summary>
        /// Note For Homepage
        /// </summary>
        [DataMember]
        public string ExternalIdentifier { get; set; }

        /// <summary>
        /// Update Custom field Values
        /// </summary>
        [DataMember]
        public List<CustomFieldValueDto> CustomFieldValues { get; set; }
    }
}
