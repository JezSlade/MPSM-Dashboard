using System;
using System.Collections.Generic;
using System.Runtime.Serialization;
using System.Text;

namespace MpsMonitor.Sdk.Models.Dto
{
    [DataContract]
    public class DealerDto : DealerBaseDto
    {
        /// <summary>
        /// 
        /// </summary>
        public DealerDto()
        {
            CustomFieldValues = new List<CustomFieldValueDto>();
        }

        /// <summary>
        /// Gets or sets the custom field values
        /// </summary>
        /// <value>
        /// The custom field values
        ///  </value>
        [DataMember]
        public IEnumerable<CustomFieldValueDto> CustomFieldValues { get; set; }


        /// <summary>
        /// Gets or sets the alternative asset description.
        /// </summary>
        /// <value>
        /// The alternative asset description.
        /// </value>
        [DataMember]
        public string AlternativeAssetDescription { get; set; }

        /// <summary>
        /// Gets or sets the telephone
        /// </summary>
        /// <value>
        /// The telephone.
        /// </value>
        [DataMember]
        public string Telephone { get; set; }

        /// <summary>
        /// Gets or sets the address
        /// </summary>
        /// <value>
        /// The address.
        /// </value>
        [DataMember]
        public string Address { get; set; }

        /// <summary>
        /// Gets or sets the vat number
        /// </summary>
        /// <value>
        /// The vat number.
        /// </value>
        [DataMember]
        public string Vat { get; set; }

        /// <summary>
        /// Abi bank coordinate
        /// </summary>
        [DataMember]
        public string Abi { get; set; }

        /// <summary>
        /// Cab bank coordinate
        /// </summary>
        [DataMember]
        public string Cab { get; set; }

        /// <summary>
        /// Gets or sets the zipcode
        /// </summary>
        /// <value>
        /// The zipcode.
        /// </value>
        [DataMember]
        public string ZipCode { get; set; }

        /// <summary>
        /// Gets or sets the province
        /// </summary>
        /// <value>
        /// The province.
        /// </value>
        [DataMember]
        public string Province { get; set; }

        /// <summary>
        /// Gets or sets the city
        /// </summary>
        /// <value>
        /// The city.
        /// </value>
        [DataMember]
        public string City { get; set; }

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
        /// Gets or sets the currency
        /// </summary>
        /// <value>
        /// The currency.
        /// </value>
        [DataMember]
        public string Currency { get; set; }

        /// <summary>
        /// Gets or sets the currency description.
        /// </summary>
        /// <value>The currency description.</value>
        [DataMember]
        public string CurrencyDescription { get; set; }

        /// <summary>
        /// Gets or sets the token
        /// </summary>
        /// <value>
        /// The token.
        /// </value>
        [DataMember]
        public string Token { get; set; }

        /// <summary>
        /// Gets or sets the codeEncrypted
        /// </summary>
        /// <value>
        /// The token.
        /// </value>
        [DataMember]
        public string CodeEncrypted { get; set; }

        /// <summary>
        /// Gets or sets the accounting email
        /// </summary>
        /// <value>
        /// The accounting email.
        /// </value>
        [DataMember]
        public string AccountingEmail { get; set; }

        /// <summary>
        /// Gets or sets the LogoUrl
        /// </summary>
        /// <value>
        /// The logo Url.
        /// </value>
        [DataMember]
        public string LogoUrl { get; set; }

        /// <summary>
        /// Gets or sets mail address
        /// </summary>
        /// <value>
        /// The mail address.
        /// </value>
        [DataMember]
        public string Mailaddress { get; set; }

        /// <summary>
        /// Gets or sets the language
        /// </summary>
        /// <value>
        /// The accounting email.
        /// </value>
        [DataMember]
        public string ShortLanguage { get; set; }

        /// <summary>
        /// Gets or sets the business nominative
        /// </summary>
        /// <value>
        /// The business nominative.
        /// </value>
        [DataMember]
        public string BusinessNominative { get; set; }

        /// <summary>
        /// Gets or sets the business gsm
        /// </summary>
        /// <value>
        /// The business gsm.
        /// </value>
        [DataMember]
        public string BusinessGsm { get; set; }

        /// <summary>
        /// Gets or sets the business email
        /// </summary>
        /// <value>
        /// The business email.
        /// </value>
        [DataMember]
        public string BusinessEmail { get; set; }

        /// <summary>
        /// Gets or sets the timezone value
        /// </summary>
        /// <value>
        /// The timezone value.
        /// </value>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// Gets or sets the technical nominative
        /// </summary>
        /// <value>
        /// The technical nominative.
        /// </value>
        [DataMember]
        public string TechnicalNominative { get; set; }

        /// <summary>
        /// Gets or sets the technical sgm
        /// </summary>
        /// <value>
        /// The technical gsm.
        /// </value>
        [DataMember]
        public string TechnicalGsm { get; set; }

        /// <summary>
        /// Gets or sets the technical email
        /// </summary>
        /// <value>
        /// The technical email.
        /// </value>
        [DataMember]
        public string TechnicalEmail { get; set; }

        /// <summary>
        /// Gets or sets the Enable customer default project setting
        /// </summary>
        /// <value>
        /// The Enable customer default project setting
        /// </value>
        [DataMember]
        public bool EnableCustomerDefaultProjectSettings { get; set; }

        /// <summary>
        /// Enable project subscription
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public bool SCEnableSubscription { get; set; }

        /// <summary>
        /// Enable eXplorer email invitation to customer
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public bool EnableEmailExplorerInstallationToCustomer { get; set; }

        /// <summary>
        /// Enable SDS
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public bool IsEnableJam { get; set; }

        /// <summary>
        /// Trial demo expiration
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public DateTime? TrialDemoExpireAt { get; set; }

        /// <summary>
        /// Enable eXplorer cleanup
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public bool EnableExplorerCleanup { get; set; }

        /// <summary>
        /// Enable eXplorer cleanup day limit
        /// </summary>
        /// <value>
        /// The day limit
        /// </value>
        [DataMember]
        public int ExplorerCleanupDayLimit { get; set; }

        /// <summary>
        /// Electronic Interchange Code
        /// </summary>
        [DataMember]
        public virtual string SDICode { get; set; }

        /// <summary>
        /// Electronic Interchange Registered Email
        /// </summary>
        [DataMember]
        public virtual string SDIPEC { get; set; }

        /// <summary>
        /// Gets or sets a value indicating whether this instance is GDRP accepted.
        /// </summary>
        /// <value>
        ///   <c>true</c> if this instance is GDRP accepted; otherwise, <c>false</c>.
        /// </value>
        [DataMember]
        public bool IsGdrpAccepted { get; set; }

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
    public class DealerListDto : DealerBaseDto
    {
        /// <summary>
        /// Gets or sets the alternative asset description.
        /// </summary>
        /// <value>
        /// The alternative asset description.
        /// </value>
        [DataMember]
        public string AlternativeAssetDescription { get; set; }

        /// <summary>
        /// Gets or sets the telephone
        /// </summary>
        /// <value>
        /// The telephone.
        /// </value>
        [DataMember]
        public string Telephone { get; set; }

        /// <summary>
        /// Gets or sets the address
        /// </summary>
        /// <value>
        /// The address.
        /// </value>
        [DataMember]
        public string Address { get; set; }

        /// <summary>
        /// Gets or sets the vat number
        /// </summary>
        /// <value>
        /// The vat number.
        /// </value>
        [DataMember]
        public string Vat { get; set; }

        /// <summary>
        /// Abi bank coordinate
        /// </summary>
        [DataMember]
        public string Abi { get; set; }

        /// <summary>
        /// Cab bank coordinate
        /// </summary>
        [DataMember]
        public string Cab { get; set; }

        /// <summary>
        /// Gets or sets the zipcode
        /// </summary>
        /// <value>
        /// The zipcode.
        /// </value>
        [DataMember]
        public string ZipCode { get; set; }

        /// <summary>
        /// Gets or sets the province
        /// </summary>
        /// <value>
        /// The province.
        /// </value>
        [DataMember]
        public string Province { get; set; }

        /// <summary>
        /// Gets or sets the city
        /// </summary>
        /// <value>
        /// The city.
        /// </value>
        [DataMember]
        public string City { get; set; }

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
        /// Gets or sets the currency
        /// </summary>
        /// <value>
        /// The currency.
        /// </value>
        [DataMember]
        public string Currency { get; set; }

        /// <summary>
        /// Gets or sets the token
        /// </summary>
        /// <value>
        /// The token.
        /// </value>
        [DataMember]
        public string Token { get; set; }

        /// <summary>
        /// Gets or sets the accounting email
        /// </summary>
        /// <value>
        /// The accounting email.
        /// </value>
        [DataMember]
        public string AccountingEmail { get; set; }

        /// <summary>
        /// Gets or sets the LogoUrl
        /// </summary>
        /// <value>
        /// The logo Url.
        /// </value>
        [DataMember]
        public string LogoUrl { get; set; }

        /// <summary>
        /// Gets or sets mail address
        /// </summary>
        /// <value>
        /// The mail address.
        /// </value>
        [DataMember]
        public string Mailaddress { get; set; }

        /// <summary>
        /// Gets or sets the language
        /// </summary>
        /// <value>
        /// The accounting email.
        /// </value>
        [DataMember]
        public string ShortLanguage { get; set; }

        /// <summary>
        /// Gets or sets the business nominative
        /// </summary>
        /// <value>
        /// The business nominative.
        /// </value>
        [DataMember]
        public string BusinessNominative { get; set; }

        /// <summary>
        /// Gets or sets the business gsm
        /// </summary>
        /// <value>
        /// The business gsm.
        /// </value>
        [DataMember]
        public string BusinessGsm { get; set; }

        /// <summary>
        /// Gets or sets the business email
        /// </summary>
        /// <value>
        /// The business email.
        /// </value>
        [DataMember]
        public string BusinessEmail { get; set; }

        /// <summary>
        /// Gets or sets the timezone value
        /// </summary>
        /// <value>
        /// The timezone value.
        /// </value>
        [DataMember]
        public string TimeZone { get; set; }

        /// <summary>
        /// Gets or sets the technical nominative
        /// </summary>
        /// <value>
        /// The technical nominative.
        /// </value>
        [DataMember]
        public string TechnicalNominative { get; set; }

        /// <summary>
        /// Gets or sets the technical sgm
        /// </summary>
        /// <value>
        /// The technical gsm.
        /// </value>
        [DataMember]
        public string TechnicalGsm { get; set; }

        /// <summary>
        /// Gets or sets the technical email
        /// </summary>
        /// <value>
        /// The technical email.
        /// </value>
        [DataMember]
        public string TechnicalEmail { get; set; }

        /// <summary>
        /// Gets or sets the Enable customer default project setting
        /// </summary>
        /// <value>
        /// The Enable customer default project setting
        /// </value>
        [DataMember]
        public bool EnableCustomerDefaultProjectSettings { get; set; }

        /// <summary>
        /// Enable project subscription
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public bool SCEnableSubscription { get; set; }

        /// <summary>
        /// Enable eXplorer email invitation to customer
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public bool EnableEmailExplorerInstallationToCustomer { get; set; }

        /// <summary>
        /// Enable SDS
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public bool IsEnableJam { get; set; }

        /// <summary>
        /// Trial demo expiration
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public DateTime? TrialDemoExpireAt { get; set; }

        /// <summary>
        /// Enable eXplorer cleanup
        /// </summary>
        /// <value>
        /// The value
        /// </value>
        [DataMember]
        public bool EnableExplorerCleanup { get; set; }

        /// <summary>
        /// Enable eXplorer cleanup day limit
        /// </summary>
        /// <value>
        /// The day limit
        /// </value>
        [DataMember]
        public int ExplorerCleanupDayLimit { get; set; }

    }

    /// <summary>
    /// 
    /// </summary>
    /* Questa anagrafica è condivisa con tutti i profili non inserire dati sensibili del dealer */
    [DataContract]
    public class DealerBaseDto : EntityDto
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
        public string Description { get; set; }
    }

}
